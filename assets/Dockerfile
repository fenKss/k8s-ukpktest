FROM nginx:alpine

RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime

RUN addgroup  assets
RUN adduser --home /var/www/assets  --gecos "First Last,RoomNumber,WorkPhone,HomePhone" --disabled-password --ingroup assets assets

RUN echo "#assets without password" >> /etc/sudoers
RUN echo "assets ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

COPY ./assets /tmp/assets
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 9000

ENTRYPOINT ["nginx", "-g", "daemon off;"]
