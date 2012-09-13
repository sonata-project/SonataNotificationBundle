Command Line
============

The notification bundle comes with one command which listen to new incoming messages::

    app/console sonata:notification:start --env=prod --iteration=250

This command must be started with the production environment to limit memory usage of
debugging information.

The ``iteration`` option is the number of iteration the command can accept before exiting.
This iteration value must be set to avoid memory limit or other issues related to php
and long running processes.

Monitoring process : Supervisord
--------------------------------

This command cannot be used or started as it on a production server. The task must be supervised by a process control system.
There are many solution available, here a solution with supervisord:

Supervisor is a client/server system that allows its users to monitor and control a number of processes on UNIX-like operating systems::

    [program:sonata_production_sonata_notification]
    command=/home/org.sonata-project.demo/current/app/console sonata:notification:start --env=prod --iteration=250
    autorestart=true
    user=www-data
    redirect_stderr=false
    stdout_logfile=/home/org.sonata-project.demo/logs/sonata_notification.log
    stdout_logfile_maxbytes=10MB

If you are deploying with capistrano, you can restart the supervisor process with a custom task::

    after "deploy:symlink" do
        run "supervisorctl -u user -p password restart sonata_production_sonata_notification"
    end

Clean up messages
-----------------

You might want to clean old messages from differents backend (if ever a backend old them)::

    app/console sonata:notification:cleanup --env=prod

Restart erroneous messages
--------------------------

In case of getting messages with an erroneous status, you can reset their statuses and they will be reprocessed during
the next iteration (this command must be used for the database backend)::

    app/console sonata:notification:restart --type="xxx" --type="yyy" --max-attempts=10