# u2f-security-bundle-demo

This repository is a fully functionnal demo for the bundle [mbarbey/u2f-security-bundle](https://github.com/mbarbey/u2f-security-bundle).

## 1. Installation

The only things you need to do is :
1) download the repository
2) Do a composer install
3) create a vhost with the folowwing content :
  ```
  <VirtualHost *:443>
    ServerName u2f.local
    DocumentRoot "/var/www/u2f/public"
	
	SSLEngine on
	SSLCertificateFile "/var/www/u2f/ssl/u2f.local.crt"
	SSLCertificateKeyFile "/var/www/u2f/ssl/u2f.local.key"
  </VirtualHost>
  ```
  Don't forget to adapt the paths.
  
  _Tips: The self signed certificates provided in the directory "ssl" are already configured for the domaine name "u2f.local" so you don't need to generate yourself your own certificate. But if you want, you can still do it yourself and use another domain name._

4) go to https://u2f.local and play with your U2F key

## 2. Functionalities in this demo

- [X] Basic user registration
- [X] Basic login with Symfony Firewall and Security
- [X] Register one or multiple security keys to account
- [X] Automatically force user to use a security key after login if the user has at least one linked ot it account
- [X] Detect when the user fail to authenticate with it key 3 times
- [X] Detect when the user try to leave the U2F authentication page without being successfully authenticated
- [ ] Prevent a user from using a key even if there is some keys linked to the account
- [ ] Prevent a user from registering a key
