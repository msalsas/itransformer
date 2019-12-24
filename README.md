Itransformer.es
===========

Itransformer.es is an online free application made for image processing and transforming. The application lets you to apply several kind of basic transformations, filters and effects.


Starting date
----

17-05.2013


Contributing
----

If you want to contribute just push your commits. I'll be grateful.


License
----

All of Itransformer.es's original code is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE, for details see LICENSE. Some third party libraries are licensed under other, compatible open source libraries. Licensing information is included in those files.


How to install
----

The following are instructions working on Apache2 systems.

###1. Download the Itransformer.es sources to your computer. Using Git:

    git clone https://msalsas@github.com/msalsas/itransformer.git

###2. Cd to where you have your sources using your terminal/command line.

    cd itransformer

###3. Create the upload directory "usuarios" and give Apache group permissions:

	mkdir usuarios
	sudo chown root:www-data usuarios/
	sudo chmod 0775 usuarios/

###4. Install the third party bundles with composer.

    composer install

###5. Create the database with Doctrine.

    php bin/console doctrine:database:create

###6. Copy the .env file into .env.local and add your database user and password, as well as the just created database name.

    cp .env .env.local
    vi .env.local

    DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name

###7. Run Symfony server.

    php bin/console server:run

###8. And that's all. Go to `localhost:8000` to see the web application running.

Testing
----

    ./bin/phpunit
