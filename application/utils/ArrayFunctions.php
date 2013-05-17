<?php

class ArrayFunctions
{
    
    /**
     * Flattens multi dimensional array.
     * Example $array:
     * array(
     *   'username'   => 'example',
     *   'registered' => array(
     *       'ip'   => '127.0.0.1',
     *       'time' => array(
     *           '0' => '1339635855',
     *           '1' => '1339635855'
     *        )
     *    )
     * );
     * Result:
     * array(
     *   'username'          => 'example',
     *   'registered_ip'     => '127.0.0.1',
     *   'registered_time_0' => '1339635855'
     *   'registered_time_1' => '1339635855'
     * );
     * 
     * @param array $array
     * @param array $skip                 - array keys to skip (skipped values will be serialized!)
     * @return array
     */
    static public function flatten( array $array, array $skip = array() )
    {
        $result = array();
        
        foreach ( $array as $key => $value ) {
            
            if ( in_array( $key, $skip, true ) === false ) {
                
                if ( is_array( $value ) ) {
                    
                    foreach ( $value as $k => $v ) {
                        
                        if ( is_array( $v ) ) {
                            
                            $v = self::flatten( $v, $skip );
                        }
                        $result[ $key . '_' . $k ] = $v;
                    }
                    
                    $result = self::flatten( $result, $skip );
                } else {
                    
                    $result[ $key ] = $value;
                }
            } else {
                
                $result[ $key ] = is_serialized( $value ) ? $value : serialize( $value );
            }
        }
        
        return $result;
    }
    
    public static function lowerKeys( $array )
	{
        foreach ( $array as $k => $v ) {

            if ( is_array( $v ) ) {

                $v = self::lowerKeys( $v );
            }

            unset( $array[ $k ] );

            $array[ strtolower( $k ) ] = $v;
        }
        
        return $array;
    }
    
    public static function lowerValues( $array )
	{
        foreach ( $array as &$v ) {

            if ( is_array( $v ) ) {

                $v = lowerValues( $v );
            } else {

                $v = strtolower( $v );
            }
        }
        
        return $array;
    }
    
    public static function implode( $glue, $pieces )
    {
        if ( !is_array( $pieces ) ) {
            
            return $pieces;
        }
        
        foreach( $pieces as $r_pieces ) {
            
            if( is_array( $r_pieces ) ) {
                
                $retVal[] = self::implode( $glue, $r_pieces );
            } else {
                
                $retVal[] = $r_pieces;
            }
        }
        
        return implode( $glue, $retVal );
    }
}

function is_serialized( $value )
{
    return ( $value == 'b:0;' || @unserialize( $value ) !== false );
}
