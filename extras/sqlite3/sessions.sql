CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE sessions(
  id TEXT PRIMARY KEY,
  data TEXT,
  last_updated DATETIME DEFAULT(CURRENT_TIMESTAMP),
  deleted_at TEXT NULL
);
CREATE TABLE users(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user VARCHAR(32),
  password VARCHAR(128)
);
