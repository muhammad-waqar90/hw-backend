FROM php:8.0-fpm-alpine

# Copy startup script
COPY ./docker/scheduler/start.sh /usr/local/bin/start
RUN chmod u+x /usr/local/bin/start

ENTRYPOINT [ "sh" ]
CMD ["/usr/local/bin/start"]