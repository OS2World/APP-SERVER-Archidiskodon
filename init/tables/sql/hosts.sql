DROP TABLE IF EXISTS n_detected_servers;

CREATE TABLE n_detected_servers (
  id int(10) NOT NULL auto_increment,

  addr char(15) NOT NULL,
  hostname char(255) NOT NULL,
  online int(10) NOT NULL default 1,

  reindexed_at timestamp NOT NULL,

  folders_total bigint NOT NULL,
  files_total bigint NOT NULL,
  bytes_total bigint NOT NULL,

  PRIMARY KEY (id),
  KEY (online, hostname)
);

-- * * * --

DROP TABLE IF EXISTS n_detector_report;

CREATE TABLE n_detector_report (
  finished_at timestamp NOT NULL
);
