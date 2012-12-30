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