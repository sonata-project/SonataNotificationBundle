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
    command=/home/org.sonata-project.demo/current/app/console sonata:notification:start --env=notification --iteration=250
    autorestart=true
    user=www-data
    redirect_stderr=false
    stdout_logfile=/home/org.sonata-project.demo/logs/sonata_notification.log
    stdout_logfile_maxbytes=10MB

If you are deploying with capistrano, you can restart the supervisor process with a custom task::

    after "deploy:create_symlink" do
        run "supervisorctl -u user -p password restart sonata_production_sonata_notification"
    end
    
.. note::

    By default, the Symfony2 provides a cross finger log handler. This handler is not suitable for
    long run processes as each log entry will be stacked into memory. So the notification process can stop
    with a memory usage error. To solve this, just create a new env called notification without this handler.


Clean up messages
-----------------

You might want to clean old messages from differents backend (if ever a backend old them)::

    app/console sonata:notification:cleanup --env=prod

Restart erroneous messages
--------------------------

In case of getting messages with an erroneous status, you can reset their statuses and they will be reprocessed during
the next iteration (this command must be used for the database backend)::

    app/console sonata:notification:restart --type="xxx" --max-attempts=10

You can get this command to run continuously with the --pulling option and you can set the delay between the time the
message has been set to error and the time the message can be reprocess with --attempt-delay option (in seconds)

    app/console sonata:notification:restart --type="xxx" --pulling --max-attempts=10 --attempt-delay=60
