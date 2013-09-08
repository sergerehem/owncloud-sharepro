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

use OCP\App;
use OCP\Util;

if (!App::isEnabled("appframework")) {
    $msg = "Can not enable sharePro because App Framework is not enabled";
    Util::writeLog("sharepro", $msg, Util::ERROR);
    return;
}

if (!App::isEnabled("user_ldap")) {
    $msg = "Can not enable sharePro because LDAP user backend is not enabled";
    Util::writeLog("sharepro", $msg, Util::ERROR);
    return;
}

Util::addScript("sharepro", "sharepro");
