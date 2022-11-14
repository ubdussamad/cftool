FROM Ubuntu:20.04
ENV TZ=EST5EDT
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt update

RUN apt install -y python3, git
RUN apt install -y apache2
RUN apt install -y php libapache2-mod-php php-mcrypt php-mysql
RUN apt install -y python3-pip, sqlite3
RUN git clone https://github.com/ubdussamad/jnu.git

RUN apt install -y  python3-networkx
RUN apt install -y  python3-igraph


RUN pip3 install -y  db-sqlite3