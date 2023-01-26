FROM yiisoftware/yii2-php:8.1-apache

WORKDIR /app

RUN docker-php-ext-install sockets

# TODO: XDEBUG_MODE=coverage or xdebug.mode=coverage has to be set (for coverage tests)
# RUN pecl install xdebug \
# 	&& docker-php-ext-enable xdebug

COPY . /app

#CMD ["/app/yii", "queue/listen"]
