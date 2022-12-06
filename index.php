<html>
  <head>
    <title>
      validate-php
    </title>
  </head>
  <body>
    <h1>validate-php</h1>
    <?php
      use wrdickson\validatephp\Validate;
      require 'lib/Validate.php';

      $d_string = '2022-08-27';


      $d = date_parse($d_string);
      echo "<PRE>";
      echo print_r($d);
      echo "</PRE>";

      $start_date = new DateTime($d_string);
      $formatted_start = date_format($start_date, 'Y-m-d');
      $end_date =  date_modify($start_date, '+17 day');
      $formatted_end = date_format($end_date,'Y-m-d');

      echo 'start formatted: ' . $formatted_start . '<br>';
      echo 'end formatted: '. $formatted_end . '<br>';


      echo '<h3>end is before start</h3>';
      if($formatted_start < $formatted_end) {
        echo 'less';
      }

      echo "<PRE>";
      echo print_r($end_date);
      echo "</PRE>";

      echo '<hr>';

      $test_values = array(
        'id'=> '21',
        'a_string' => 'a1',
        'p_word' => '123
        ',
        'start_date' => '2022-09-15',
        'end_date' => '2022-10-16'
      );


      $options = array (
        'id' => array (
          'is_integer',
          'is_greater_than_5'
        ),
        'a_string' => array (
          'is_alphanum',
          'is_length, 2, 10'
        ),
        'p_word' => array (
          'is_alphanum_dash_star_underscore',
          'is_length, 8, 24'
        ),
        'start_date' => array (
          'is_YYYY_dash_MM_dash_DD_format',
          'formatted_date_is_less_than, ' . $test_values['end_date']
        ),
        'end_date' => array (
          'is_YYYY_dash_MM_dash_DD_format'
        )
      );



/*
      $validate = new Validate( $test_values, $options );

      echo'<PRE>';
      print_r( $validate->validate() );
      echo'</PRE>';
*/

      Class ExtendedValidate extends Validate {
        // this has to be public function when we extend
        // in the base class, these kinds of functions are private
        public function formatted_date_is_less_than( $input, $args_array ) {
          $end_date = $args_array[0];
          $s = new Datetime($input);
          $e = new DateTime($end_date);
          if( $s < $e ) {
            return array(
              'test' => true
            );
          } else {
            //  return the args to the passing_tests array
            $a_str = '';
            foreach($args_array as $index => $arg_string) {
              if( $index == 0) {
                $a_str = $a_str . $arg_string;
              } else {
                $a_str = $a_str . ',' . $arg_string;
              }
            }

            return array(
              'test' => false,
              'error' => 'fails formatted_date_is_less_than args:' . $a_str
            );
          }
        }

      }

      $eValidate = new ExtendedValidate( $test_values, $options );
      echo'<PRE>';
      print_r( $eValidate->validate() );
      echo'</PRE>';




    ?>
  </body>
</html>