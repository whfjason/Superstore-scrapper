<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
  </head>
  <body>
    <?php

      $host = "LOCALHOST";
      $database = "DATABASE";
      $user = "USER";
      $password = "PASSWORD";

      $connection = mysqli_connect($host, $user, $password, $database);
      $erorr = mysqli_connect_error();

      if ($error != null)
      {
        echo "<p>Unable to connect to database $error </p>";
        exit();
      }
      else
      {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $filename = 'diaper-product-superstore.txt';
        $content = file($filename);
        foreach ($content as $url)
        {
          $url = trim(preg_replace('/\s\s+/', '', $url));
          curl_setopt($ch, CURLOPT_URL, $url);
          $result = curl_exec($ch);
          if(curl_error($ch))
          {
            echo 'Error: ' . curl_error($ch) . '<br />';
            exit();
          }
          elseif (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200)
          {
            echo 'HTTP status code ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ' returned' . '<br />';
            exit();
          }
          else
          {
            sleep(rand(10, 23));
            $id = ""; $timestamp = ""; $name = ""; $price = ""; $imagePath = ""; $brand = ""; $webpath = $url;
            $timestamp = date('y-m-d h:i:s');
            echo $timestamp . '<br />';
            preg_match('/\d+?._EA/', $url, $match);
            $id = $match[0];
            echo $id . '<br />';
            if (strlen($id) == 0) { continue; }
            $imageProductId = substr($id, 0, -3);
            $imagePath = 'https://assets.shop.loblaws.ca/products/'.$imageProductId.'/b1/en/front/'.$imageProductId.'_front_a06.png';
            echo $imagePath . '<br />';

            $dom = new DOMDocument();
            @$dom -> loadHTML($result);
            $productDetailNodes = $dom -> getElementsByTagName('h1');
            foreach ($productDetailNodes as $element)
            {
              $brand = $element -> getElementsByTagName('span')[0] -> nodeValue;
              $brand = trim(preg_replace('/\s\s+/', '', $brand));
              $name = $element -> nodeValue;
              $name = trim(preg_replace('/\s\s+/', '', $name));
              echo $brand . '<br />';
              echo $name . '<br />';
            }
            $productPriceNodes = $dom -> getElementsByTagName('span');
            foreach ($productPriceNodes as $element)
            {
              if ($element -> getAttribute('class') == 'reg-price-text')
              {
                if (strlen($element -> nodeValue) < 1) { continue; }
                $price = $element -> nodeValue;
                if ($price == 0)
                {
                  if ($element -> getAttribute('class') == 'sale-price-text')
                  {
                    if (strlen($element -> nodeValue) < 1) { continue; }
                    $price = $element -> nodeValue;
                    echo $price . '<br />';
                    break;
                  }
                }
                else
                {
                  echo $price . '<br />';
                  break;
                }
              }
            }
            $sql = "INSERT INTO Products VALUES ('".addslashes($name)."', '".$price."', '".$timestamp."', '".$id."', '".addslashes($brand)."', '".$imagePath."', '".$webpath."')";
            if (mysqli_query($connection, $sql))
            {
              echo "Product $id has been successfully inserted to the database <br /><br />";
            }
            else
            {
              echo 'An error occured while inserting data: ' . mysqli_error($connection) . ' <br />';
              exit();
            }
          }
        }
        curl_close($ch);
        mysqli_close($connection);
      }
    ?>
  </body>
</html>
