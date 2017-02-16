FROM centos:7

# Install Nginx
RUN rpm -Uvh http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm
RUN yum update -y && \
    yum install -y \
    nginx \
    net-tools

# Clean Up
RUN yum clean all && rm -rf /tmp/nginx*

COPY nginx.conf /etc/nginx/nginx.conf
COPY test.com.key /etc/nginx/ssl/test.com.key
COPY test.com.crt /etc/nginx/ssl/test.com.crt

COPY gencert.sh /
RUN chmod u+x /gencert.shcd 


EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]
