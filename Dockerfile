# Installing Base container.
FROM Ubuntu:20.04
# Change timezone to your local timezone by changing the TZ variable.
# For a list of valid timezones, see https://en.wikipedia.org/wiki/List_of_tz_database_time_zones
ENV TZ=EST5EDT
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt update


# Installing Dependencies
RUN apt install -y python3, git
RUN apt install -y apache2
RUN apt install -y php libapache2-mod-php php-mcrypt php-mysql
RUN apt install -y python3-pip, sqlite3
RUN apt install -y  python3-networkx
RUN apt install -y  python3-igraph
RUN pip3 install -y  db-sqlite3

# Copying files to container
RUN git clone https://github.com/ubdussamad/cftool.git
COPY ./cftool /var/www/html



RUN echo "ServerName 0.0.0.0" >> /etc/apache2/apache2.conf
RUN service apache2 restart
EXPOSE 80
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]