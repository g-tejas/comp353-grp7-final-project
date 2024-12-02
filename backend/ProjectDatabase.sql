CREATE TABLE IF NOT EXISTS `member` (
  `Member_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Password` varchar(255) NOT NULL,
  `Date_of_Birth` date NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Pseudonym` varchar(50) NOT NULL,
  `Is_Business` set('Yes','No') NOT NULL DEFAULT 'No',
  `Post_Fee` decimal(10,2) DEFAULT NULL,
  `Privilege_Level` int(1) NOT NULL DEFAULT 1,
  `Status` enum('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`Member_ID`) USING BTREE,
  UNIQUE KEY `Email` (`Email`)
) ;

CREATE TABLE IF NOT EXISTS `group` (
  `Group_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`Group_ID`) USING BTREE
) ;

CREATE TABLE IF NOT EXISTS `content` (
  `Content_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Group_ID` int(11) DEFAULT NULL,
  `Member_ID` int(11) NOT NULL,
  `Body` text DEFAULT NULL,
  `Title` varchar(50) NOT NULL,
  `Media_Path` varchar(255) DEFAULT NULL,
  `Timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `Is_Event` bit(1) NOT NULL DEFAULT b'0',
  `Event_Date_and_time` datetime DEFAULT NULL,
  `Event_Location` tinytext DEFAULT NULL,
  PRIMARY KEY (`Content_ID`) USING BTREE,
  KEY `Member_ID` (`Member_ID`) USING BTREE,
  KEY `GID` (`Group_ID`) USING BTREE,
  CONSTRAINT `Content-Group_ID` FOREIGN KEY (`Group_ID`) REFERENCES `group` (`Group_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `Content-Member_ID` FOREIGN KEY (`Member_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ;


CREATE TABLE IF NOT EXISTS `comment` (
  `Content_ID` int(11) NOT NULL,
  `Member_ID` int(11) NOT NULL,
  `Body` text NOT NULL,
  `Timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Content_ID`,`Member_ID`,`Timestamp`) USING BTREE,
  KEY `CID` (`Content_ID`) USING BTREE,
  KEY `ID` (`Member_ID`) USING BTREE,
  CONSTRAINT `comment-Content_ID` FOREIGN KEY (`Content_ID`) REFERENCES `content` (`Content_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `comment-Member_ID` FOREIGN KEY (`Member_ID`) REFERENCES `member` (`Member_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ;



CREATE TABLE IF NOT EXISTS `content_classification` (
  `Content_ID` int(11) NOT NULL,
  `View` enum('Public','Private') NOT NULL,
  `Allow_Comment` bit(1) NOT NULL DEFAULT b'0',
  `Allow_Link` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`Content_ID`) USING BTREE,
  CONSTRAINT `content_classification_Content_ID` FOREIGN KEY (`Content_ID`) REFERENCES `content` (`Content_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
);


CREATE TABLE IF NOT EXISTS `event_votes` (
  `Member_ID` int(11) NOT NULL,
  `Content_ID` int(11) NOT NULL,
  `Option_Chosen` int(11) DEFAULT NULL,
  PRIMARY KEY (`Member_ID`,`Content_ID`),
  KEY `event_vote-Content-ID` (`Content_ID`),
  CONSTRAINT `event_vote-Content-ID` FOREIGN KEY (`Content_ID`) REFERENCES `content` (`Content_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `event_vote-Member_ID` FOREIGN KEY (`Member_ID`) REFERENCES `member` (`Member_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ;





CREATE TABLE IF NOT EXISTS `group_members` (
  `Member_ID` int(11) NOT NULL,
  `Group_ID` int(11) NOT NULL,
  `Is_Owner` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`Member_ID`,`Group_ID`) USING BTREE,
  KEY `group_members-GID` (`Group_ID`) USING BTREE,
  CONSTRAINT `group_members-Group_ID` FOREIGN KEY (`Group_ID`) REFERENCES `group` (`Group_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `group_members-Member_ID` FOREIGN KEY (`Member_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ;




CREATE TABLE IF NOT EXISTS `member_relationship` (
  `Member_1_ID` int(11) NOT NULL,
  `Member_2_ID` int(11) NOT NULL,
  `Type` enum('Family','Friend','Colleague') DEFAULT 'Friend',
  PRIMARY KEY (`Member_1_ID`,`Member_2_ID`),
  KEY `Member_2_ID` (`Member_2_ID`),
  CONSTRAINT `Member_1_ID` FOREIGN KEY (`Member_1_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Member_2_ID` FOREIGN KEY (`Member_2_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ;


CREATE TABLE IF NOT EXISTS `private_messages` (
  `Sender_ID` int(11) NOT NULL,
  `Receiver_ID` int(11) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `Body` text DEFAULT NULL,
  `Timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Sender_ID`,`Receiver_ID`,`Timestamp`),
  KEY `Reciever_ID` (`Receiver_ID`),
  CONSTRAINT `Receiver_ID` FOREIGN KEY (`Receiver_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Sender_ID` FOREIGN KEY (`Sender_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ;


CREATE TABLE IF NOT EXISTS `profile_accessibility` (
  `Member_ID` int(11) NOT NULL,
  `Target_ID` int(11) NOT NULL,
  `Accessibility` enum('Blocked','Private','Public') NOT NULL DEFAULT 'Private',
  PRIMARY KEY (`Member_ID`,`Target_ID`),
  KEY `profile_accessibility-Target_ID` (`Target_ID`),
  CONSTRAINT `profile_accessibility-Member_ID` FOREIGN KEY (`Member_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `profile_accessibility-Target_ID` FOREIGN KEY (`Target_ID`) REFERENCES `member` (`Member_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ;


