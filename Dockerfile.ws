FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    procps \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY monitoring/ /app/

CMD ["php", "ws_server.php"] 