DROP TABLE IF EXISTS n_started_robots;

CREATE TABLE n_started_robots (
  id int(10) NOT NULL auto_increment,

  robot_pid int(10) NOT NULL,
  robot_name char(16) NOT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY (robot_pid)
);
