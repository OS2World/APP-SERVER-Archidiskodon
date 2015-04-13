DROP TABLE IF EXISTS n_file;

CREATE TABLE n_file (
  id int(10) NOT NULL auto_increment,
  type char(1) NOT NULL default '-',
  ext_id int(10) NOT NULL,
  server_id int(10) NOT NULL,

  name char(255) NOT NULL,
  datetime timestamp NOT NULL,
  size int(10) NOT NULL default 0,
  location text NOT NULL,
  explanation text NOT NULL,

  PRIMARY KEY (id),
  KEY (name, datetime),
  KEY (type, ext_id, server_id)
);

DROP TABLE IF EXISTS n_extension;

CREATE TABLE n_extension (
  id int(10) NOT NULL auto_increment,

  type char(1) NOT NULL default '-',
  ext char(4) NOT NULL,

  PRIMARY KEY (id),
  KEY (ext)
);
