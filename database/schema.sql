CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    is_legacy_user INTEGER DEFAULT 0
);


INSERT INTO users (username, password_hash, is_legacy_user) 
VALUES ('v13thun9', '0e830400451993494058024219903391', 1);


INSERT INTO users (username, password_hash, is_legacy_user)
VALUES ('admin', '$2y$10$8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM', 0);

INSERT INTO users (username, password_hash, is_legacy_user)
VALUES ('guest', '$2y$10$8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM', 0);

INSERT INTO users (username, password_hash, is_legacy_user)
VALUES ('test', '$2y$10$8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM', 0);

INSERT INTO users (username, password_hash, is_legacy_user)
VALUES ('user', '$2y$10$8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM', 0);

INSERT INTO users (username, password_hash, is_legacy_user)
VALUES ('demo', '$2y$10$8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM8K3h5z8/.8K1p/a0dR1xqM', 0); 