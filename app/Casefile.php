<?php
//namespace App\Casefile;
use App\Libraries\Twig;

class Casefile
{

    public static function getCaseOld($case_id = 0)
    {
        global $dbh;

        // $loader = new \Twig\Loader\ArrayLoader([
        //     'index' => 'Hello {{ name }}!',
        // ]);
        // $twig = new \Twig\Environment($loader);
        // echo $twig->render('index', ['name' => 'Fabien']);





        //Get case data
        $q = $dbh->prepare("SELECT * FROM cm WHERE id = ?");
        $q->bindParam(1, $case_id);
        $q->execute();
        $case_data = $q->fetch(PDO::FETCH_ASSOC);

        //Get columns config
        $q = $dbh->prepare("SELECT * from cm_columns ORDER BY display_order ASC");
        $q->execute();
        $columns = $q->fetchAll(PDO::FETCH_ASSOC);

        $data = [];

        foreach ($columns as $col) {
            //push the value of the field in case_data onto $columns
            if ($col['db_name'] !== 'assigned_users') {
                //we don't want assigned users in this view
                $field = $col['db_name'];
                $field_value = $case_data[$field];
                $col['value'] = $field_value;
                $data[] = $col;
            }
        }


        return $data;

        // if (!$_SESSION['mobile']) {
        //     include '../../../html/templates/interior/cases_case_data.php';
        // }


    }

    public static function getCase($case_id = 0)
    {
        global $dbh;

        // $loader = new \Twig\Loader\ArrayLoader([
        //     'index' => 'Hello {{ name }}!',
        // ]);
        // $twig = new \Twig\Environment($loader);
        // echo $twig->render('index', ['name' => 'Fabien']);


        //Get case data
        $q = $dbh->prepare("SELECT * FROM cm WHERE id = ?");
        $q->bindParam(1, $case_id);
        $q->execute();
        $case_data = $q->fetch(PDO::FETCH_ASSOC);

        // prd($case_data);


        return $case_data ? $case_data : [];





        // //Get columns config
        // $q = $dbh->prepare("SELECT * from cm_columns ORDER BY display_order ASC");
        // $q->execute();
        // $columns = $q->fetchAll(PDO::FETCH_ASSOC);
        //
        // $data = [];
        //
        // foreach ($columns as $col) {
        //     //push the value of the field in case_data onto $columns
        //     if ($col['db_name'] !== 'assigned_users') {
        //         //we don't want assigned users in this view
        //         $field = $col['db_name'];
        //         $field_value = $case_data[$field];
        //         $col['value'] = $field_value;
        //         $data[] = $col;
        //     }
        // }


        // return $data;

        // if (!$_SESSION['mobile']) {
        //     include '../../../html/templates/interior/cases_case_data.php';
        // }


    }


}
