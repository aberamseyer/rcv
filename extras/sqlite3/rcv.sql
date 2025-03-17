CREATE TABLE `bible_concordance`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `word` varchar(32) NOT NULL
  ,
  `status` integer NOT NULL DEFAULT '1'
);
CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE `bible_concordance_to_chapter_contents`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `concordance_id` integer NOT NULL
  ,
  `chapter_contents_id` integer NOT NULL
);
CREATE TABLE `chapter_contents`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `chapter_id` integer NOT NULL
  ,
  `number` integer NOT NULL
  ,
  `content` text NOT NULL
  ,
  `tier` integer DEFAULT NULL
  ,
  `reference` varchar(24) DEFAULT NULL
  ,
  `sort_order` integer NOT NULL
  ,
  `outline_order` integer NOT NULL
);
CREATE TABLE `chapters`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `book_id` integer NOT NULL
  ,
  `number` integer NOT NULL
  ,
  `verses` integer NOT NULL
);
CREATE TABLE `concordance`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `word` varchar(64) DEFAULT NULL
  ,
  `matches` integer DEFAULT NULL
  ,
  `references` text
);
CREATE TABLE `footnote_concordance`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `word` varchar(32) NOT NULL
  ,
  `status` integer NOT NULL DEFAULT '1'
);
CREATE TABLE `footnote_concordance_to_footnotes`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `footnote_concordance_id` integer NOT NULL
  ,
  `footnotes_id` integer NOT NULL
);
CREATE TABLE `footnotes`(
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
  ,
  `status` integer NOT NULL DEFAULT '1'
  ,
  `verse_id` integer NOT NULL
  ,
  `position` varchar(12) NOT NULL
  ,
  `cross_reference` text NOT NULL
  ,
  `note` text NOT NULL
  ,
  `matching_word` varchar(64) DEFAULT NULL
  ,
  `number` varchar(3) DEFAULT NULL
  ,
  `letter` varchar(1) DEFAULT NULL
);
CREATE INDEX "idx_chapters_book_id" ON "chapters"(`book_id`);
CREATE INDEX "idx_bible_concordance_to_chapter_contents_concordance_id" ON "bible_concordance_to_chapter_contents"(
  `concordance_id`
);
CREATE INDEX "idx_bible_concordance_to_chapter_contents_chapter_contents_id" ON "bible_concordance_to_chapter_contents"(
  `chapter_contents_id`
);
CREATE INDEX "idx_bible_concordance_word" ON "bible_concordance"(`word`);
CREATE INDEX "idx_concordance_word" ON "concordance"(`word`);
CREATE INDEX "idx_footnote_concordance_to_footnotes_footnote_concordance_id" ON "footnote_concordance_to_footnotes"(
  `footnote_concordance_id`
);
CREATE INDEX "idx_footnote_concordance_to_footnotes_footnotes_id" ON "footnote_concordance_to_footnotes"(
  `footnotes_id`
);
CREATE INDEX "idx_footnote_concordance_word" ON "footnote_concordance"(`word`);
CREATE INDEX "idx_chapter_contents_chapter_id" ON "chapter_contents"(
  `chapter_id`
);
CREATE INDEX "idx_chapter_contents_reference" ON "chapter_contents"(
  `reference`
);
CREATE INDEX "idx_chapter_contents_content" ON "chapter_contents"(`content`);
CREATE INDEX "idx_footnotes_verse_id" ON "footnotes"(`verse_id`);
CREATE INDEX "idx_footnotes_note" ON "footnotes"(`note`);
CREATE INDEX "idx_footnotes_cross_reference" ON "footnotes"(`cross_reference`);
CREATE TABLE books(
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  name varchar(32) NOT NULL,
  chapters integer NOT NULL,
  testament integer NOT NULL,
  abbreviation varchar(12) NOT NULL,
  details text NOT NULL,
  type STRING,
  sort_order integer DEFAULT NULL
);
CREATE INDEX idx_books_name ON books(name);
