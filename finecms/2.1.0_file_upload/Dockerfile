FROM janes/alpine-lamp
MAINTAINER janes - https://github.com/hxer/docker-vulnapp

COPY www/ /var/www/localhost/htdocs/

RUN chown -R apache:apache /var/www/localhost/htdocs
RUN mysql -uroot -e "create database finecms;"
