CREATE TABLE tx_elearning_domain_model_course (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL DEFAULT '0',
  sys_language_uid int(11) NOT NULL DEFAULT '0',
  l10n_parent int(11) unsigned NOT NULL DEFAULT '0',
  l10n_diffsource mediumblob,
  title varchar(255) NOT NULL DEFAULT '',
  slug varchar(2048) NOT NULL DEFAULT '',
  teaser text,
  description text,
  authors varchar(255) NOT NULL DEFAULT '',
  image int(11) unsigned NOT NULL DEFAULT '0',
  published tinyint(1) unsigned NOT NULL DEFAULT '0',
  sorting int(11) NOT NULL DEFAULT '0',
  tstamp int(11) unsigned NOT NULL DEFAULT '0',
  crdate int(11) unsigned NOT NULL DEFAULT '0',
  cruser_id int(11) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
  hidden tinyint(1) unsigned NOT NULL DEFAULT '0',
  starttime int(11) unsigned NOT NULL DEFAULT '0',
  endtime int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY parent (pid)
);

CREATE TABLE tx_elearning_domain_model_lesson (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL DEFAULT '0',
  sys_language_uid int(11) NOT NULL DEFAULT '0',
  l10n_parent int(11) unsigned NOT NULL DEFAULT '0',
  l10n_diffsource mediumblob,
  course int(11) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  slug varchar(2048) NOT NULL DEFAULT '',
  content text,
  type varchar(20) NOT NULL DEFAULT 'content',
  video_url varchar(2048) NOT NULL DEFAULT '',
  file int(11) unsigned NOT NULL DEFAULT '0',
  link_url varchar(2048) NOT NULL DEFAULT '',
  duration_minutes int(11) NOT NULL DEFAULT '0',
  published tinyint(1) unsigned NOT NULL DEFAULT '0',
  sorting int(11) NOT NULL DEFAULT '0',
  tstamp int(11) unsigned NOT NULL DEFAULT '0',
  crdate int(11) unsigned NOT NULL DEFAULT '0',
  cruser_id int(11) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
  hidden tinyint(1) unsigned NOT NULL DEFAULT '0',
  starttime int(11) unsigned NOT NULL DEFAULT '0',
  endtime int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY parent (pid)
);

CREATE TABLE tx_elearning_domain_model_question (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL DEFAULT '0',
  lesson int(11) unsigned NOT NULL DEFAULT '0',
  question_text text,
  sorting int(11) NOT NULL DEFAULT '0',
  tstamp int(11) unsigned NOT NULL DEFAULT '0',
  crdate int(11) unsigned NOT NULL DEFAULT '0',
  cruser_id int(11) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
  hidden tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY parent (pid)
);

CREATE TABLE tx_elearning_domain_model_answer (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL DEFAULT '0',
  question int(11) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  is_correct tinyint(1) unsigned NOT NULL DEFAULT '0',
  sorting int(11) NOT NULL DEFAULT '0',
  tstamp int(11) unsigned NOT NULL DEFAULT '0',
  crdate int(11) unsigned NOT NULL DEFAULT '0',
  cruser_id int(11) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
  hidden tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY parent (pid)
);

CREATE TABLE tx_elearning_domain_model_progress (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL DEFAULT '0',
  fe_user int(11) unsigned NOT NULL DEFAULT '0',
  lesson int(11) unsigned NOT NULL DEFAULT '0',
  completed tinyint(1) unsigned NOT NULL DEFAULT '0',
  completed_at int(11) unsigned NOT NULL DEFAULT '0',
  quiz_passed tinyint(1) unsigned NOT NULL DEFAULT '0',
  quiz_passed_at int(11) unsigned NOT NULL DEFAULT '0',
  last_quiz_failed_at int(11) unsigned NOT NULL DEFAULT '0',
  last_visited_at int(11) unsigned NOT NULL DEFAULT '0',
  tstamp int(11) unsigned NOT NULL DEFAULT '0',
  crdate int(11) unsigned NOT NULL DEFAULT '0',
  cruser_id int(11) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
  hidden tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY parent (pid)
);

CREATE TABLE tx_elearning_course_favorite (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL DEFAULT '0',
  fe_user int(11) unsigned NOT NULL DEFAULT '0',
  course int(11) unsigned NOT NULL DEFAULT '0',
  tstamp int(11) unsigned NOT NULL DEFAULT '0',
  crdate int(11) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
  hidden tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  UNIQUE KEY user_course (fe_user, course),
  KEY parent (pid)
);
