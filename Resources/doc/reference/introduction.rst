Introduction
============

The notification bundle allows to generate message which can be retrieve by a generic backend which can
then start specific action. This bundle does not try to reproduce a real message queue system as this bundle
can be used with a message queue system.

$publisher->create('email', array(
    'from' => array(
        'email' => 'no-reply@sonata-project.org',
        'name'  => 'No Reply'
    ),
    'to'   => array(
        array('email' => 'myuser@example.org', 'name'  => 'My User'),
        array('email' => 'myuser1@example.org', 'name'  => 'My User 1'),
    ),
    'message' => array(
        'html' => '<b>hello</b>',
        'text' => 'hello'
    ),
    'subject' => 'Contact form',
));

$handlerChain->register('email', $service1);
$handlerChain->register('email', $service2);