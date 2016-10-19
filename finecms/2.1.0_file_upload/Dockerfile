FROM janes/alpine-lamp
MAINTAINER janes - https://github.com/hxer/docker-vulnapp

COPY www/ /var/www/localhost/htdocs/

RUN rm /var/www/localhost/htdocs/index.html
RUN chown -R apache:apache /var/www/localhost/htdocs

RUN sed -i '\/tail -f \/var\/log\/apache2\/access.log/i\sleep 3 && mysql -uroot -e \"create database finecms;\"' /start.sh
