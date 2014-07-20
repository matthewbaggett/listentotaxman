<?php
namespace ListenToTaxman;

use GuzzleHttp\Client as GuzzleClient;


class Query{

  const LISTEN_TO_TAXMAN_URI = 'http://www.listentotaxman.com/index.php';

  static public function request($email, $income, $year ){
    // Add this to allow your app to use Guzzle and the Cookie Plugin.

    $request_array = array(
      'body' => array(
        'add' => 0,
        'age' => 0,
        'c' => 1,
        'calculate' => 'Calculate',
        'childcare' => 0,
        'childcare_freq' => 12,
        'code' => '',
        'email' => $email,
        'ingr' => $income,
        'pension' => 0,
        'time' => 1,
        'vw' => array('yr','mth','wk'),
        'year' => $year,
      )
    );

    $client = new GuzzleClient();
    $response = $client->post(
      self::LISTEN_TO_TAXMAN_URI,
      $request_array
    );
    //\Kint::dump($response);exit;

    $html = str_get_html($response->getBody());
    $data = new \StdClass();
    $data->income_tax = array();

    $data->taxable_year                 = self::listentotaxman_parse_value($html->find('tr.row-taxable td.yr', 0)->innertext);
    $data->net                          = self::listentotaxman_parse_value($html->find('tr.row-net-wage td.yr', 0)->innertext);
    $data->personal_national_insurance  = self::listentotaxman_parse_value($html->find('tr.row-ni td.yr', 0)->innertext);
    $data->employers_national_insurance = self::listentotaxman_parse_value($html->find('tr.row-employers-ni td.yr', 0)->innertext);
    $data->income_tax['20%']            = self::listentotaxman_parse_value($html->find('tr#row-taxband-0 td.yr', 0)->innertext);
    $data->income_tax['40%']            = self::listentotaxman_parse_value($html->find('tr#row-taxband-1 td.yr', 0)->innertext);
    $data->income_tax['45%']            = self::listentotaxman_parse_value($html->find('tr#row-taxband-2 td.yr', 0)->innertext);
    $data->personal_total_deductions    = self::listentotaxman_parse_value($html->find('tr.row-total-deductions td.yr', 0)->innertext);

    return $data;
  }

  private static function listentotaxman_parse_value($money){
    return trim(str_replace(",", "", str_replace("&pound;", "", trim(strip_tags($money)))));
  }
}