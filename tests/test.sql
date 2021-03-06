USE `kacela-test`;

DROP TABLE peeps;
DROP TABLE contacts;
DROP TABLE tests;

CREATE TABLE contacts (
	email_address VARCHAR(150) NOT NULL PRIMARY KEY,
	street VARCHAR(150) NULL,
	phone CHAR(14) NOT NULL
) ENGINE=INNODB;

CREATE TABLE peeps (
	`code` CHAR(8) NOT NULL PRIMARY KEY,
	fname VARCHAR(150) NOT NULL,
	lname VARCHAR(150) NOT NULL,
	email VARCHAR(150) NULL,
	CONSTRAINT `fk-contact-peep` FOREIGN KEY (email) REFERENCES contacts(email_address) ON DELETE SET NULL
) ENGINE=INNODB;

CREATE TABLE tests (
	id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	test_name VARCHAR(255) NOT NULL,
	started TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	completed TIMESTAMP NULL,
	flagged BOOL NOT NULL DEFAULT 0
) ENGINE=INNODB;