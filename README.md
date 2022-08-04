# UMBC-Submit
Web Portal Overhaul for the Submit System at UMBC

Welcome to the UMBC Submit Project! The idea behind this was to make a web portal where students, TAs, and instructors can manage the code submission part of a course. It will be designed as a GUI for the existing GL system at UMBC. A lot of students have trouble using the command line at first, which negatively impacts their performance in the course. We want to make it as easy as possible for students to succeed in the class by making it easy to submit their assignments.

## Feature Map
- Students would be able to log in and upload/submit their files through the website portal, as well as see what they have submitted and even run their code with sample IO to make sure that it works. 
- Teaching Assisstants will be able to log on to the website and see what they still need to grade. It will also allow them to grade assignments right there by entering point totals in a rubric. 
- Instructors have the ability to add/remove students and TAs from a course database (SQL), as well as create and manage assignments. 

## Setup Instructions for local web server

Because authentication using UMBC credentials is an integral part of this web application, you cannot currently host it on your local machine.

~~1. Install PHP if you have not done so already.~~
~~2. Install SQL (MySQL, MariaDB, etc.) if you have not done so already.~~
~~3. Create an SQL database that is relevant to your course (like `cmsc201_database`)~~
~~4. Clone the repository into a folder of your choice.~~
~~5. Open the `db_sql.php` file and edit the four variables to fit the specifications of your SQL server database.~~
~~6. Open your terminal/command prompt and navigate to the folder where you cloned the repository.~~
~~7. Run `php -S localhost:8000` in order to start the PHP Server with the Document Root in that repository.~~
~~8. Open your favorite web browser and navigate to [http://localhost:8000/](http://localhost:8000/).~~
~~9. You should be good to go!~~

## Future Work
Since I have graduated from UMBC, I will not be working on or maintaining this project any more. The latest live version is hosted at [https://www.submit.cs.umbc.edu], and you must be on the UMBC VPN in order to access it.
