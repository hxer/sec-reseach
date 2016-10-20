FROM janes/alpine-lamp
MAINTAINER janes - https://github.com/hxer/docker-vulnapp

RUN rm -f /var/www/localhost/htdocs/index.html

COPY www/ /var/www/localhost/htdocs/
RUN chown -R apache:apache /var/www/localhost/htdocs

# create database finecms while running
RUN sed -i '\/tail -f \/var\/log\/apache2\/access.log/i\sleep 3 && mysql -uroot -e \"create database finecms;\"' /start.sh
