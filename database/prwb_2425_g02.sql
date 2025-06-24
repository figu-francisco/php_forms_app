drop database if exists `prwb_2425_g02`;
CREATE
    DATABASE IF NOT EXISTS `prwb_2425_g02`;
USE
    `prwb_2425_g02`;

CREATE TABLE users
(
    id        INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255)                    NOT NULL UNIQUE,
    email     VARCHAR(255)                    NOT NULL UNIQUE,
    password  VARCHAR(255)                    NOT NULL,
    role      enum ('user', 'admin', 'guest') NOT NULL
);

CREATE TABLE forms
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    owner       INT          NOT NULL,
    is_public   tinyint      NOT NULL DEFAULT 1, 
    FOREIGN KEY (owner) REFERENCES users (id) on delete cascade,
    unique(owner, title)  
);

CREATE TABLE form_colors
(
    form        INT                                     NOT NULL,
    color       enum('blue', 'red', 'green', 'yellow')  NOT NULL,
    primary key (form, color),
    FOREIGN KEY (form) REFERENCES forms (id) on delete cascade
);

CREATE TABLE user_form_accesses
(
    user        INT                    NOT NULL,
    form        INT                    NOT NULL,
    access_type enum ('user','editor') NOT NULL,
    primary key (user, form),
    FOREIGN KEY (user) REFERENCES users (id) on delete cascade,
    FOREIGN KEY (form) REFERENCES forms (id) on delete cascade
);


CREATE TABLE questions
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    form        INT                               NOT NULL,
    idx         INT                               NOT NULL,
    title       VARCHAR(255)                      NOT NULL,
    description TEXT,
    type        enum ('short', 'long', 'date', 'email', 'mcq_single', 'mcq_multiple') NOT NULL, 
    required    BOOLEAN                           NOT NULL,
    FOREIGN KEY (form) REFERENCES forms (id) on delete cascade,
    unique (form, idx),
    unique (form, title)
);

CREATE TABLE instances
(
    id        INT AUTO_INCREMENT PRIMARY KEY,
    form      INT       NOT NULL,
    user      INT       NOT NULL,
    started   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    completed TIMESTAMP null DEFAULT null,
    FOREIGN KEY (form) REFERENCES forms (id) on delete cascade,
    FOREIGN KEY (user) REFERENCES users (id) on delete cascade
);

CREATE TABLE option_values
(
    label       VARCHAR(255)    NOT NULL,
    question    INT             NOT NULL,
    idx         INT             NOT NULL,
    PRIMARY KEY (question, idx),
    UNIQUE (question, label),
    FOREIGN KEY (question) REFERENCES questions (id) on delete cascade
);

CREATE TABLE answers
(
    instance INT  NOT NULL,
    question INT  NOT NULL,
    value    TEXT NOT NULL,
    idx      INT  NOT NULL DEFAULT -1, 
    primary key (instance, question, idx), 
    FOREIGN KEY (instance) REFERENCES instances (id) on delete cascade,
    FOREIGN KEY (question) REFERENCES questions (id) on delete cascade
);