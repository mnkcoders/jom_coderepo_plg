<?php defined('_JEXEC') or die;
/**
 * @package     CODERS.Repository
 * @subpackage  CODERS.Repository
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class PlgSystemCodeRepo extends JPlugin {
    /**
     * @var array
     */
    private $_dependencies = array(
        'repository',
        'resource'
    );

    /**
     * @return boolean
     */
    function onAfterRoute() {
        
        $this->preload();
        //$rid = $this->request();
        $rid = \CODERS\Repository\Repository::request();
        
        if( $rid !== FALSE ){
            
            //\CODERS\Repository\Repository::download($rid);
            $this->output($rid);
            
            exit;
        }

        return TRUE;
    }
    /**
     * @return int
     */
    private final function getExpiration( $days = 30){

        return gmdate('D, d M Y H:i:s', time() + ( $this->getMaxAge($days ) * 1000 ) );
    }
    /**
     * @return int
     */
    private final function getMaxAge( $days = 30 ){
        return 60 * 60 * 24 * $days;
    }
    /**
     * @return \PlgSystemCodeRepo
     */
    private final function preload(){
        
        //var_dump(JPATH_COMPONENT_ADMINISTRATOR);
        //var_dump(class_exists(\CODERS\Repository\Repository::class));
        //$base = preg_replace('/\\\\/', '/', JPATH_COMPONENT_ADMINISTRATOR);
        $base = preg_replace('/\\\\/', '/', JPATH_ADMINISTRATOR );

        foreach( $this->_dependencies as $class ){
            $path = sprintf('%s/components/com_coderepo/classes/%s.class.php',$base,$class);
            require_once( $path );
        }
        
        return $this;
    }
    /**
     * @return array
     */
    private static final function headers( $name , $type , $size , $attachment = FALSE ){
        
        return array(
            sprintf( 'Content-Type: %s' , $type ),
            sprintf( 'Content-Disposition: %s; filename="%s"', $attachment ? 'attachment' : 'inline', $name ),
            sprintf( 'Content-Length: %s', $size ),

            //'Cache-Control: no-cache, must-revalidate',
            //'Pragma: no-cache',
            'Expires: Sat, 27 May 2102 16:55:21 GMT',
            'Cache-Control: public, max-age=2592000',
            //sprintf( 'Expires: %s GMT' , $this->getExpiration( ) ),
            //sprintf( 'Cache-Control: public, max-age=%s', $this->getMaxAge( ) ),
        );
    }
    /**
     * @param string $resource_id
     */
    private final function output( $resource_id ){

        $resource = \CODERS\Repository\Repository::load($resource_id);

        if( $resource !== FALSE ){

            $headers = $this->headers(
                    $resource->name,
                    $resource->type, 
                    $resource->size,
                    $resource->isAttachment( ) );
            $buffer = $resource->read();
            
            foreach( $headers as $header ){
                header( $header );
            }

            print $buffer;
        }
        else{
            print 'INVALID RESOURCE';
        }
        return $this;
    }
    /**
     * @return string|boolean
     */
    private final function request(){
        
        return class_exists(\CODERS\Repository\Repository::class) ?
                \CODERS\Repository\Repository::request() :
                FALSE;

    }
}
