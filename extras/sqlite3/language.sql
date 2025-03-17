CREATE TABLE original_text(
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  text_content TEXT
);
CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE lexicon(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  strongs TEXT,
  word TEXT,
  pronunciation TEXT,
  translit TEXT,
  definition TEXT,
  usages TEXT,
  occurances TEXT,
  frequency INTEGER
);
CREATE INDEX idx_lexicon_strongs ON lexicon(strongs);
CREATE INDEX idx_lexicon_word ON lexicon(word);
