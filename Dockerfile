# Installing Base container.
FROM ubuntu:20.04
# Change timezone to your local timezone by changing the TZ variable.
# For a list of valid timezones, see https://en.wikipedia.org/wiki/List_of_tz_database_time_zones
ENV TZ=EST5EDT
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt update


# Installing Dependencies
RUN apt install -y python3 git
RUN apt install -y python3-pip sqlite3
RUN apt install -y apache2
RUN apt install -y php libapache2-mod-php
RUN python3 -m pip install networkx==2.8
RUN python3 -m pip install igraph==0.9.10
RUN python3 -m pip install db-sqlite3

# Copying files to container
RUN git clone https://github.com/ubdussamad/cftool.git
RUN rm -rf /var/www/html
RUN mv cftool /var/www/html
RUN chmod -R 777 /var/www/html

RUN echo "ServerName 0.0.0.0" >> /etc/apache2/apache2.conf
RUN service apache2 restart
EXPOSE 80
RUN chmod -R 777 /var/www/html
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]


# Random Details about using docker which you will forget.

#for i in $(docker container ls --format "{{.ID}}"); do docker inspect -f '{{.State.Pid}} {{.Name}}' $i; done

# Build the docker image by running the following command: docker build -t name_your_image path2dockerfile
# Run the docker image by running the following command: docker run -dit -p 80:80 name_your_image
# List the running containers by running/stopped the following command: docker ps -a
# Stop the running container by running the following command: docker stop container_id
# Remove the stopped container by running the following command: docker rm container_id
# Remove the docker image by running the following command: docker rmi image_id
# Remove all the stopped containers by running the following command: docker rm $(docker ps -a -q)
# Remove all the docker images by running the following command: docker rmi $(docker images -q)
# Start the stopped container by running the following command: docker start container_id
# Attach shell to the running container by running the following command: docker exec -it container_id /bin/bash