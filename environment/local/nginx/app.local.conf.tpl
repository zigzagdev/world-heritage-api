server {
listen ${PORT};
server_name _;

include /etc/nginx/app.common.conf;

add_header Content-Security-Policy "default-src 'self' http://host.docker.internal:5173; img-src 'self' data:; font-src 'self' https://fonts.bunny.net; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://host.docker.internal:5173; connect-src 'self' ws://host.docker.internal:5173 http://host.docker.internal:5173; frame-ancestors 'self'" always;
}
