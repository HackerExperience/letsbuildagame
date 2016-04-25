-- CREATE DATABASE lbag;

CREATE TABLE users (
    user_id         SERIAL PRIMARY KEY,
    username        VARCHAR NOT NULL UNIQUE,
    email           VARCHAR NOT NULL UNIQUE,
    password        VARCHAR,
    provider        VARCHAR NOT NULL default 'backend',
    date_registered TIMESTAMP NOT NULL default now()
);


CREATE TABLE projects (
    project_id  VARCHAR PRIMARY KEY,
    name        VARCHAR NOT NULL UNIQUE,
    icon        VARCHAR,
    color       VARCHAR
);

INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_DEV', NULL, NULL);
INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_ART', NULL, NULL);
INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_MGT', NULL, NULL);
INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_GD', NULL, NULL);
INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_TRANSLATION', NULL, NULL);
INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_PATRON', NULL, NULL);
INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_STUDENT', NULL, NULL);
INSERT INTO "projects" ("name", "icon", "color") VALUES ('TEAM_GAMER', NULL, NULL);


CREATE TABLE user_projects (
    project_id    INTEGER NOT NULL REFERENCES projects,
    user_id       INTEGER NOT NULL REFERENCES users,
    is_subscribed BOOLEAN NOT NULL,
    PRIMARY KEY (project_id, user_id)
);

CREATE TABLE newsletter (
    user_id   INTEGER REFERENCES users (user_id),
    frequency INTEGER NOT NULL
)

CREATE TABLE email_verification (
    user_id         SERIAL PRIMARY KEY,
    email           VARCHAR NOT NULL UNIQUE,
    code            VARCHAR NOT NULL UNIQUE
);
