# COMP353

## Group 7

| Name | Student ID | Email |
|------|------------|--------|
| Jihan Moon | 40170485 | mjh000602@gmail.com |
| Geeneth Kulatunge | 40195350 | kulatungegeeneth@gmail.com |
| Tejas Garrepally | 40322722 | e0957366@u.nus.edu |
| Arthur Mosnier | 40122836 | a_mosnie_live_concordia_ca |

Project Description)

The objective of our project was to design a relational database system for a Private Online Community Social Network System (COSN). In response to the many concerns about online social media and community services controlled by big companies that use them to mine user data and lack transparency in many aspects, our design provides local communities with an open source system that runs on a local server.

The application is a two-tier system, which supports any popular web browser at the client side and secure http server with PHP parser and a MySQL database at the server side.

The main idea of the implementation is to create a community, controlled and supervised by an Admin user.
In this community, there will be many groups, where users can post text, video and images. Other users that are members of a specific group may comment on other group member’s posts, if the original poster allows it.
Users will also add other users as friends and can message each other through private messages. 
Users have status and privilege. Privilege can be Junior (by default, it is the lowest privilege and only allows joining groups, adding friends, messaging friends, posting and commenting in groups they are a member of), Senior (in addition to all the the junior privileges, seniors can create groups making them the owner of such groups) and Admin (admins can change the privilege and status of any user and delete groups, they also need to approve all the content in the community). Status can be active, inactive (user will not be visible to other users) or suspended (user cannot log in until admin allows it by changing his status). 
On their homepage, each user can see their posts, the newest posts in the groups they are members of and all the posts and comments made by friends (whose profile is set to public for the user).



LINK TO DATABASE DIAGRAM:
https://liveconcordia-my.sharepoint.com/:u:/r/personal/a_mosnie_live_concordia_ca/Documents/ER%20Digram%20COMP353.vsdx?d=we16ba84417cd4e828e2ce06c4bdee7f0&csf=1&web=1&e=fUZh0q

LINK TO DATABASE SCHEMA (TABLES AND ATTRIBUTES):
https://docs.google.com/document/d/1sVznHG634ye395hFJMW3sg32vMHFekyLOGuRRLCKAwg/edit?usp=sharing

PHP:
- Server-side scripting: PHP processes user requests, interacts with the database, and generates dynamic HTML content.
- Security: Implements security measures like input validation, output escaping, and session management to protect user data and prevent attacks.
- User Authentication and Authorization: Manages user logins, password hashing, and access control to different parts of the system.
- Community Features: Develops features like discussion forums, file sharing, and messaging systems.

MySQL:
- Data Storage: Stores user information, community data, posts, comments, and other relevant data.
- Data Retrieval: Fetches data from the database to populate web pages and enable user interactions.

HTML:

- User Interface: Creates the structure and layout of web pages.
- Content Display: Displays user-generated content, such as posts, comments, and files.
- Form Handling: Processes user input from forms and sends it to the PHP server for processing.


## Technologies used:
- PHP
- HTML
- MySQL Database
