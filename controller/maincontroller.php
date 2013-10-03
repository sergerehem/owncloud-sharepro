<?php
/**
 * ownCloud - sharePro
 *
 * @author Aleksandr Tsertkov
 * @copyright 2013 Aleksandr Tsertkov tsertkov@gmail.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\sharepro\Controller;

use OCA\AppFramework\Controller\Controller;
use OCA\AppFramework\Core\API;
use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Http\JSONResponse;
use OCA\sharepro\lib\LdapEmailSearch;
use OCA\user_ldap\lib\Connection as LdapConnection;

class MainController extends Controller
{
    private $ldapEmailSearch;
    private $nameAttr;
    private $emailAttr;

    /**
     * @param API $api
     * @param Request $request
     * @param LdapEmailSearch $emailSearch
     * @param string $nameAttr
     * @param string $emailAttr
     */
    public function __construct(API $api, Request $request, LdapEmailSearch $emailSearch
        , $nameAttr, $emailAttr)
    {
        parent::__construct($api, $request);
        $this->ldapEmailSearch = $emailSearch;
        $this->nameAttr = $nameAttr;
        $this->emailAttr = $emailAttr;
    }

    /**
     * Search for email with query input parameter
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     *
     * @return JSONResponse
     */
    public function emailSearch(){
        $query = $this->params("query");

        if ($query === NULL) {
            return new JSONResponse();
        }

        $exclude = array(); 
        $query = str_replace(array(',',';'), ' ', $query); // replaces all ',' and ';' by ' '
        $query = trim(preg_replace('/\s+/',' ', $query));  // remove extra whitespaces
        $pos = strrpos($query, '@');
        if ($pos !== false) {
          $posBlank = strpos($query, ' ', $pos);
          if ($posBlank !== false) {
             $query = trim(substr($query, $posBlank));
           }
        }
        
        // return earlier if nothing to search
        if (!strlen($query)) {
            return new JSONResponse();
        }

        $records = $this->ldapEmailSearch->queryEmails($query, $exclude);

        // construct response array
        $users = array();
        foreach ($records as $record) {
            $name = $record[$this->nameAttr];
            $email = $record[$this->emailAttr];
            $label = "$name ($email)";

            $users[] = array("value" => $email, "label" => $label);
        }

        return new JSONResponse(array("data" => $users));
    }
}
