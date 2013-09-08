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

use OCA\AppFramework\Http\JSONResponse;
use OCA\AppFramework\Utility\ControllerTestUtility;
use OCA\sharepro\Controller\MainController;

require_once __DIR__ . "/../loader.php";

class MainControllerTest extends ControllerTestUtility
{
    private $api;
    private $ldapEmailSearch;

    public function setUp()
    {
        $this->api = $this->getAPIMock();
        $this->ldapEmailSearch = $this->getMockBuilder('\OCA\sharepro\lib\LdapEmailSearch')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testEmailSearch()
    {
        $this->ldapEmailSearch->expects($this->once())
            ->method("queryEmails")
            ->with($this->equalTo("john"))
            ->will($this->returnValue(array(array(
                "cn" => "John Smith",
                "mail" => "john_oc@mailinator.com",
            ))));

        $controller = $this->getController(array("params" => array("query" => "john")));
        $response = $controller->emailSearch();
        $this->assertTrue($response instanceof JSONResponse);
    }

    private function getController($params)
    {
        $request = $this->getRequest($params);
        return new MainController($this->api, $request, $this->ldapEmailSearch, "cn", "mail");
    }
}
 