PRAGMA foreign_keys = ON;

CREATE TABLE users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT NOT NULL,
    email         TEXT NOT NULL UNIQUE,
    phone         TEXT,
    password_hash TEXT NOT NULL,
    role          TEXT NOT NULL DEFAULT 'customer' CHECK (role IN ('customer','admin')),
    status        TEXT NOT NULL DEFAULT 'active'   CHECK (status IN ('active','locked')),
    created_at    TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);


CREATE TABLE genres (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE movies (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        TEXT NOT NULL,
    description  TEXT,
    poster       TEXT,
    trailer_url  TEXT,
    director     TEXT,
    cast_list    TEXT,
    age_rating   TEXT DEFAULT 'P',
    duration     INTEGER NOT NULL CHECK (duration > 0),
    release_date TEXT,
    status       TEXT NOT NULL DEFAULT 'now_showing' CHECK (status IN ('now_showing','upcoming','hidden')),
    created_at   TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);

CREATE TABLE movie_genres (
    movie_id INTEGER NOT NULL REFERENCES movies(id) ON DELETE CASCADE,
    genre_id INTEGER NOT NULL REFERENCES genres(id),
    PRIMARY KEY (movie_id, genre_id)
);

CREATE TABLE cinemas (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    name    TEXT NOT NULL,
    address TEXT,
    phone   TEXT,
    status  TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive'))
);

CREATE TABLE rooms (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    cinema_id INTEGER NOT NULL REFERENCES cinemas(id),
    name      TEXT NOT NULL,
    status    TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive'))
);

CREATE TABLE seats (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id     INTEGER NOT NULL REFERENCES rooms(id),
    seat_row    TEXT NOT NULL,
    seat_number INTEGER NOT NULL,
    seat_type   TEXT NOT NULL DEFAULT 'standard' CHECK (seat_type IN ('standard','vip')),
    status      TEXT NOT NULL DEFAULT 'active'   CHECK (status IN ('active','locked')),
    UNIQUE (room_id, seat_row, seat_number)
);

CREATE TABLE showtimes (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    movie_id      INTEGER NOT NULL REFERENCES movies(id),
    room_id       INTEGER NOT NULL REFERENCES rooms(id),
    start_time    TEXT NOT NULL,
    end_time      TEXT NOT NULL,
    base_price    INTEGER NOT NULL CHECK (base_price >= 0),
    vip_surcharge INTEGER NOT NULL DEFAULT 0 CHECK (vip_surcharge >= 0),
    status        TEXT NOT NULL DEFAULT 'scheduled' CHECK (status IN ('scheduled','cancelled')),
    CHECK (end_time > start_time)
);
CREATE INDEX idx_showtimes_room_time ON showtimes(room_id, start_time, end_time);
CREATE INDEX idx_showtimes_movie     ON showtimes(movie_id, start_time);

CREATE TABLE bookings (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id      INTEGER NOT NULL REFERENCES users(id),
    showtime_id  INTEGER NOT NULL REFERENCES showtimes(id),
    booking_code TEXT NOT NULL UNIQUE,
    total_amount INTEGER NOT NULL CHECK (total_amount >= 0),
    status       TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','confirmed','cancelled','expired')),
    expires_at   TEXT NOT NULL,
    created_at   TEXT NOT NULL DEFAULT (datetime('now','localtime')),
    cancelled_at TEXT
);
CREATE INDEX idx_bookings_user ON bookings(user_id, created_at);

CREATE TABLE booking_seats (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id  INTEGER NOT NULL REFERENCES bookings(id),
    showtime_id INTEGER NOT NULL REFERENCES showtimes(id),
    seat_id     INTEGER NOT NULL REFERENCES seats(id),
    price       INTEGER NOT NULL CHECK (price >= 0),
    active      INTEGER NOT NULL DEFAULT 1 CHECK (active IN (0,1))
);
CREATE UNIQUE INDEX uq_active_seat ON booking_seats(showtime_id, seat_id) WHERE active = 1;

CREATE TABLE payments (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id       INTEGER NOT NULL REFERENCES bookings(id),
    transaction_code TEXT NOT NULL UNIQUE,
    amount           INTEGER NOT NULL CHECK (amount >= 0),
    method           TEXT NOT NULL DEFAULT 'qr_demo',
    status           TEXT NOT NULL DEFAULT 'pending'
                     CHECK (status IN ('pending','processing','success','failed','expired','refunded')),
    created_at       TEXT NOT NULL DEFAULT (datetime('now','localtime')),
    paid_at          TEXT
);
CREATE INDEX idx_payments_booking ON payments(booking_id);

CREATE TABLE payment_webhook_events (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id         TEXT NOT NULL UNIQUE,
    payment_id       INTEGER REFERENCES payments(id),
    transaction_code TEXT NOT NULL,
    event_type       TEXT NOT NULL,
    payload          TEXT,
    received_at      TEXT NOT NULL DEFAULT (datetime('now','localtime')),
    processed_at     TEXT
);

CREATE TABLE settings (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL
);
