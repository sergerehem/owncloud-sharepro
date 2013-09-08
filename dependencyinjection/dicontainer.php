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

namespace OCA\sharepro\DependencyInjection;

use OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;
use OCA\sharepro\Controller\MainController;
use OCA\sharepro\lib\LdapEmailSearch;
use OCP\Util;
use OCA\sharepro\Core\API;

class DIContainer extends BaseContainer
{
    public function __construct(){
        parent::__construct("sharepro");

        $this["API"] = $this->share(function($c){
            return new API($c["AppName"]);
        });

        $this["MainController"] = function ($c){
            $nameAttr = $c["LdapConnection"]->ldapUserDisplayName;
            $emailAttr = $c["LdapConnection"]->ldapEmailAttribute;
            return new MainController($c["API"], $c["Request"], $c["LdapEmailSearch"]
                , $nameAttr, $emailAttr);
        };

        $this["LdapEmailSearch"] = $this->share(function (){
            return new LdapEmailSearch($this["LdapConnection"]);
        });

        $this["LdapConnection"] = $this->share(function ($c){
            $usedBackends = $c["API"]->getUserBackendNames();
            $supportedBackends = array(
                'OCA\user_ldap\USER_LDAP',
                'OCA\user_ldap\User_Proxy',
            );
            $backend = array_intersect($supportedBackends, $usedBackends);
            $backend = reset($backend);

            // exit earlier is not supported used backend found
            if (!$backend) {
                $msg = "No supported user backend for SharePro available";
                Util::writeLog("sharepro", $msg, Util::ERROR);
                return;
            }

            // extract user backend
            $rprop = new \ReflectionProperty('\OC_USER', "_usedBackends");
            $rprop->setAccessible(true);
            $usedBackends = $rprop->getValue();
            $ldapUserBackend = $usedBackends[$backend];

            // extract ldap connection using reflection
            // there are no other way to reuse ldap resource
            $robj = new \ReflectionObject($ldapUserBackend);

            // check for use_ldap backend
            if ($robj->hasProperty("connection")) {
                $rprop = $robj->getProperty("connection");
                $rprop->setAccessible(true);
                $connection = $rprop->getValue($ldapUserBackend);
                return $connection;
            }

            // user_proxy first server connection only used
            $rprop = new \ReflectionProperty('\OCA\user_ldap\lib\Proxy', "connectors");
            $rprop->setAccessible(true);
            $connectors = $rprop->getValue();
            $connection = reset($connectors);
            return $connection;
        });
    }
}
