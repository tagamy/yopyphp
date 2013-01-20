CREATE TABLE user (
	user_id int not null,
	screen_name varchar(255),
	authority tinyint not null default 0, -- 1:管理者, 0:子ユーザー
	state tinyint not null default 1, -- 1:有効, 0:無効
	create_on datetime not null,
	modify_on timestamp not null,
	PRIMARY KEY (user_id),
	INDEX k1 (create_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE blog (
	blog_id int not null auto_increment,
	user_id int not null,
	title varchar(255) not null,
	description text not null,
	stete tinyint not null default 1,
	create_on datetime not null,
	modify_on timestamp not null,
	PRIMARY KEY (blog_id),
	INDEX k1 (user_id),
	INDEX k2 (create_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;