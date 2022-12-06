<?php

namespace wrdickson\validatephp;

Class Validate {
  /*  Example options:
        $options = array (
          'id' => array (
            'is_integer'
          ),
          'a_string' => array (
            'is_alphanum',
            //  note here how params are appended
            'is_length, 2, 10'
          ),
          'b_string' => array (
            'is_alphanum_dash_star_underscore'
          )
        );
  */
  private $options;
  /*  Example test_values:
        $test_values = array(
          'id'=> '2',
          'a_string' => 'a1',
          'b_string' => 'xxxx'
        );
  */
  private $test_values;
  //  holds an array of errors as the validator exectues
  private $errors = array();
  //  boolean, fails if ANY test fails or if there are problems detected with the data
  private $valid = true;
  //  each passing test logs to this array
  private $passing_tests = array();
  //  can be used to watch things in debugs
  private $console = array();


  /**
   * CONSTRUCTOR
   * 
   * @param $test_values array see example in definitions above
   * @param options array see examples in definitions above
   */
  public function __construct ( $test_values, $options) {
    $this->options = $options;
    $this->test_values = $test_values;

  }

  public function validate () {
    //  existence of the params
    if( !$this->options ) {
      array_push( $this->errors, 'options not provided' );
      $this->valid = false;
    }
    if( !$this->test_values ) {
      array_push( $this->errors, 'test_values not provided' );
      $this->valid = false;
    }
    // iterate through the options
    foreach( $this->options as $key => $option ) {
      //  make sure option is an array
      if( !is_array($option) ) {
        $this->valid = false;
        array_push($this->errors, $key . ' is malformed');
      }
      //  iterate through the options array and run the tests
      foreach( $option as $rule_raw ) {
        //  explode the cdv string into functin and params
        $iArr = explode(',', $rule_raw );
        $rule = '';
        $args_arr = array();
        //  iterate through the exploded array to build $rule and $args_arr
        foreach( $iArr as $index => $value ) {
          if( $index == 0 ) {
            $rule = $iArr[0];
          } else {
            array_push($args_arr, $value);
          }
        }
        //  does the ftn exist?
        if( method_exists( $this, $rule) ) {
          //  EXECUTE THE VALIDATION FUNCTION
          //  well, first, test that the key exists in the test_values array
          if( isset( $this->test_values[$key]) ) {
            //  note we always pass args arr, even if it is empty
            $result = $this->$rule($this->test_values[$key], $args_arr);
            //  handle no result
            if( !$result ) {
              $this->valid = false;
              array_push($this->errors, 'Error testing ' . $rule );
              //  handle $input has failed validation
            } else {
              if( !$result['test'] ) {
                $this->valid = false;
                array_push($this->errors, $key . ' ' . $result['error'] );
              } elseif ( $result['test'] == true ) {
                //  return the args to the passing_tests array
                $a_str = '';
                foreach($args_arr as $index => $arg_string) {
                  if( $index == 0) {
                    $a_str = $a_str . $arg_string;
                  } else {
                    $a_str = $a_str . ',' . trim($arg_string);
                  }
                }
                array_push($this->passing_tests, $key . ' passes ' . $rule. ' args:'. $a_str );
              }
            }
          //  value does not exist in test_values array
          } else {
            $this->valid = false;
            array_push( $this->errors, $key . ' does not exist in test_values array');
          }

        //  handle the case where the validator function does not exist
        } else {
          $this->valid = false;
          array_push($this->errors, $rule . ' validation function does not exist' );
        }
      }
    }
    //  generate the return
    $r = array(
      'test_values' => $this->test_values,
      'options' => $this->options,
      'errors' => $this->errors,
      'valid' => (int)$this->valid,
      'console' => $this->console,
      'passing_tests' => $this->passing_tests
    );
    return $r;
  }

  //  VALIDATION FUNCTIONS FOLLOW:

  /**
   * is_length
   * 
   * @param $args_array[0]: int minimum length
   * @param $args_array[1]: int maximum length
   * 
   */
  private function is_length( $input, $args_array) {
    //  TODO handle missing/ malformed args???
    $min = $args_array[0];
    $max = $args_array[1];
    if( strlen($input) >= $min && strlen($input) <= $max ) {
      return array (
        'test' => true
      );
    } else {
      return array (
        'test' => false,
        'error' => 'is incorrect length'
      );
    }
  }
  /**
   * is_alphanum   use a regex to determine if the value is alphanumeric
   * 
   * @param $args_array unused
   */
  private function is_alphanum ( $input, $args_array) {
    $pattern = "/^[A-Za-z0-9]+$/";
    if( preg_match( $pattern, $input ) ) {
      return array (
        'test' => true
      );
    } else {
      return array (
        'test' => false,
        'error' => 'is not alphanumeric'
      );
    }
  }

  private function is_alphanum_dash_star_underscore ( $input, $args_array ) {
    $pattern = "/^[A-Za-z0-9_*-]+$/";
    if( preg_match( $pattern, $input )  ) {
      return array(
        'test' => true
      );
    } else {
      return array(
        'test' => false,
        'error' => 'is not alphanum dash star underscore'
      );
    }
  }

  private function is_greater_than_5 ( $input, $args_array ) {
    if(  $input > 5  ) {
      return array(
        'test' => true
      );
    } else { 
      return array(
        'test' => false,
        'error' => 'is not greater than 5'
      );
    }
  }

  private function is_integer ( $input, $args_array ) {
    if( filter_var($input, FILTER_VALIDATE_INT) ) {
      return array(
        'test' => true
      );
    } else { 
      return array(
        'test' => false,
        'error' => 'is not an integer',
      );
    }
  }

  private function is_YYYY_dash_MM_dash_DD_format ( $input, $args_array ) {
    $pattern = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
    if( preg_match( $pattern, $input )  ) {
      return array(
        'test' => true
      );
    } else {
      return array(
        'test' => false,
        'error' => 'is not YYYY-MM-DD format'
      );
    }
  }


}