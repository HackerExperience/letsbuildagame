CREATE TABLE users (
  user_id         SERIAL PRIMARY KEY,
  name            VARCHAR NOT NULL,
  email           VARCHAR NOT NULL UNIQUE,
  password        VARCHAR,
  provider        VARCHAR NOT NULL default 'backend',
  date_registered TIMESTAMP NOT NULL default now()
);


CREATE TABLE projects (
  project_id  SERIAL PRIMARY KEY,
  name        VARCHAR NOT NULL UNIQUE,
  icon        VARCHAR,
  color       VARCHAR
);


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
