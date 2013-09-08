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

namespace OCA\sharepro\lib;

use OCA\user_ldap\lib\Connection;
use OCA\user_ldap\lib\Access;

/**
 * Class for searching user emails in LDAP
 */
class LdapEmailSearch extends Access
{
    const LIMIT = null;
    protected $emailattr;
    protected $nameattr;
    protected $searchfilter;

    public function __construct(Connection $connection){
        $this->setConnector($connection);
        $this->emailattr = $connection->ldapEmailAttribute;
        $this->nameattr = $connection->ldapUserDisplayName;
        $this->searchfilter = $this->getSearchFilter();
    }

    /**
     * Search for user emails
     * @param $query search query
     * @param $excludeEmails array
     * @return array
     */
    public function queryEmails($query, array $excludeEmails = array()){
        $filter = $this->getFilter($query);
        $attrs = $this->getAttrs();
        $users = parent::searchUsers($filter, $attrs, self::LIMIT);

        // filter out entries without emails
        $users = array_filter($users, function ($v) use ($excludeEmails){
            if (!isset($v[$this->emailattr])) {
                return false;
            }

            if (in_array($v[$this->emailattr], $excludeEmails)) {
                return false;
            }

            return true;
        });

        return $users;
    }

    /**
     * @param string $query
     * @return string
     */
    protected function getFilter($query){
        return str_replace("%s", $query, $this->searchfilter);
    }

    /**
     * @return array
     */
    protected function getAttrs(){
        return array($this->nameattr, $this->emailattr);
    }

    /**
     * Construct ldap search query using user_ldap connection settings
     * @return string
     */
    private function getSearchFilter(){
        $userFilter = "({$this->connection->ldapUserFilter})";
        $attrs = $this->connection->ldapAttributesForUserSearch;
        if (!$attrs) {
            return $userFilter;
        }

        foreach ($attrs as &$v) {
            $v = "($v=%s*)";
        }

        $attrs = implode($attrs);
        $attrs = "(|$attrs)";

        return "(&{$userFilter}{$attrs})";
    }
}
