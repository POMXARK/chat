<?php

namespace Domain;

class Config
{
    const DB_CONNECTION='pgsql';
    const DB_HOST='db'; #'127.0.0.1';
    const DB_PORT='5432'; #'5433';
    const DB_DATABASE='chat'; #'postgres';
    const DB_USERNAME='postgres'; #'admin';
    const DB_PASSWORD='12345678'; #'mypass';
    const CHARSET = 'utf8';
    const DB_PREFIX = '';
}
