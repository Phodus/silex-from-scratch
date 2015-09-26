<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

define('APP_PATH', dirname(__DIR__));

$app = new Silex\Application();
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(APP_PATH . '/app/config/config.yml'));

$app['baseUrl'] = $app['config']['base_url'];
$app['debug'] = $app['config']['debug'];

$app->register(new Silex\Provider\ValidatorServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));

$app->register(new FormServiceProvider());

$app->register(new Silex\Provider\SessionServiceProvider());

// Twig Extension
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates',
));

// Routes
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', array(
               'list' => array(
                   'element 1',
                   'element 2',
                   'element 3'
               ),
            ));
});

$app->match('/add', function(Request $request) use ($app) {

	$sent = false;

	$form = $app['form.factory']->createBuilder('form')
        ->add('authorized', 'checkbox', array(
            'label'     => 'Authorized',
            'required'  => false,
            'attr'     => array('checked'   => 'checked')
        ))
		->add('method', 'choice', array(
            'attr' => array('class' => 'group-radio'),
            'choices' => array('1' => 'Choice1', '2' => 'Choice2'),
            'expanded' => true,
            'data' => '1'
        ))
        ->add('description', 'textarea', array(
            'required' => false,
			'attr' => array('class' => 'form-control', 'placeholder' => 'Enter a description')
		))
		->add('url', 'text', array(
			'constraints' => new Assert\NotBlank(),
			'attr' => array('class' => 'form-control', 'placeholder' => '/'),
            'data' => '/'
		))
		->add('add', 'submit', array(
			'attr' => array('class' => 'btn btn-primary')
		))
		->getForm();

	$form->handleRequest($request);

	if($form->isValid()) {
		$data_posted = $form->getData();

		$sent = true;
	}

	return $app['twig']->render('add.twig', array('form' => $form->createView(), 'sent' => $sent));
})->bind('add');

$app->match('/edit/{id}', function(Request $request, $id) use ($app) {

	$sent = false;

    $data_edit = $data->getCall($id);

    if($data_edit !== false) {

        $authorized = array();
        if($data_edit['authorized'] == '1') {
            $authorized = array('checked'   => 'checked');
        }

    	$form = $app['form.factory']->createBuilder('form')
            ->add('authorized', 'checkbox', array(
                'label'     => 'Authorized',
                'required'  => false,
                'attr'     => $authorized
            ))
    		->add('method', 'choice', array(
                'attr' => array('class' => 'group-radio'),
                'choices' => array('1' => 'Choice1', '2' => 'Choice2'),
                'expanded' => true,
                'data' => strtolower($data_edit['method'])
            ))
            ->add('description', 'textarea', array(
                'required' => false,
    			'attr' => array('class' => 'form-control', 'placeholder' => 'Enter a description'),
                'data' => $data_edit['description']
    		))
    		->add('url', 'text', array(
    			'constraints' => new Assert\NotBlank(),
    			'attr' => array('class' => 'form-control', 'placeholder' => '/'),
                'data' => $data_edit['url']
    		))
    		->add('edit', 'submit', array(
    			'attr' => array('class' => 'btn btn-primary')
    		))
    		->getForm();

    	$form->handleRequest($request);

    	if($form->isValid()) {
    		$data_posted = $form->getData();

    		$sent = true;
    	}

        return $app['twig']->render('edit.twig', array('id' => $id, 'form' => $form->createView(), 'sent' => $sent));
    }
    else {
        return $app['twig']->render('edit.twig', array('id' => $id, 'sent' => $sent));
    }
})->bind('edit');


return $app;
