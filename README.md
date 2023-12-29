#Dabase structure 

-- Account table
CREATE TABLE account (
  userId INT(11) PRIMARY KEY AUTO_INCREMENT,
  firstName VARCHAR(50),
  lastName VARCHAR(50),
  email VARCHAR(50),
  password VARCHAR(255),
  role VARCHAR(20) DEFAULT 'User',
  avatar VARCHAR(255),
  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Album 
Create TABLE album (
  albumId int(11) PRIMARY KEY AUTO_INCREMENT,
  userId int(11) not null,
  albumName varchar(50) not null,
  FOREIGN KEY (userId) REFERENCES account (userId) ON DELETE SET NULL;
);

-- Category
CREATE TABLE category (
  cateID int(11) PRIMARY KEY AUTO_INCREMENT,
  cateName varchar(20) not null,
  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

-- Photo table
Create TABLE photo (
  photoId int(11) PRIMARY KEY AUTO_INCREMENT,
  userId int(11) not null,
  caption varchar(100),
  description varchar(255),
  category int(11),
  photoPath varchar(255) not null,
  updateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  album int(11),
  FOREIGN KEY (album) REFERENCES album (albumId) ON DELETE SET NULL,
  FOREIGN KEY (userId) REFERENCES account (userId) ON DELETE SET NULL;
  FOREIGN KEY (category) REFERENCES category (cateID) ON DELETE SET NULL;
);

-- Comment
CREATE TABLE comment (
  cmtID int(11) PRIMARY KEY AUTO_INCREMENT,
  photoId int(11) not null,
  userId int(11) not null,
  content varchar(255) not null,
  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (userId) REFERENCES account (userId) ON DELETE SET NULL;
  FOREIGN KEY (photoId) REFERENCES photo (photoId) ON DELETE SET NULL;
)
