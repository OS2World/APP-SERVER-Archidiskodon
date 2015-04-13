DROP TABLE IF EXISTS n_dictionary;

CREATE TABLE n_dictionary (
  id int(10) NOT NULL auto_increment,

  word char(255) NOT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY (word)
);

-- * * * --

DROP TABLE IF EXISTS n_file_dictionary_link;

CREATE TABLE n_file_dictionary_link (
  id int(10) NOT NULL auto_increment,

  file_id int(10) NOT NULL,
  word_id int(10) NOT NULL,

  PRIMARY KEY (id),
  KEY (file_id, word_id),
  KEY (word_id, file_id)
);

-- * * * --

DROP TABLE IF EXISTS n_abbrev;

CREATE TABLE n_abbrev (
  id int(10) NOT NULL auto_increment,

  abbrv char(4) NOT NULL,
  length int(10) NOT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY (abbrv)
);

-- * * * --

DROP TABLE IF EXISTS n_file_abbrev_link;

CREATE TABLE n_file_abbrev_link (
  id int(10) NOT NULL auto_increment,

  file_id int(10) NOT NULL,
  abbrv_id int(10) NOT NULL,

  PRIMARY KEY (id),
  KEY (file_id, abbrv_id),
  KEY (abbrv_id, file_id)
);

