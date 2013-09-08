owncloud-sharepro
=================

**SharePro** is **ownCloud** app which adds autocomplete feature to email field in
share dropdown dialog.

Email search is performed against LDAP database which is used by LDAP user and
group backend.

![owncloud-sharepro](http://i.imgur.com/RN4BwV5.png)

## Requirements

This app requires user_ldap and appframework to be installed and enabled.

## Configuration

SharePro uses LDAP connection and configuration from user_ldap app. If several LDAP servers are configured then only the first one will be used.

Make sure you have set **User Search Attributes** and **User Display Name Field** on **Advanced** tab of **LDAP user and group backend** configuration page.