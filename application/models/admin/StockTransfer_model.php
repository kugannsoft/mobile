<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class StockTransfer_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }


     public function loadproductjson($query,$tranferDate,$fromloc,$toloc,$prlevel) {

         
         $query1 =$this->db->select('product.ProductCode,product.Prd_Description,pricestock.Price,pricestock.PSLocation,pricestock.PSPriceLevel')
         ->from('product')
      
         ->join(' pricestock', ' pricestock.PSCode = product.ProductCode', 'INNER')
         
         ->where('pricestock.PSLocation', $fromloc)
         ->where('pricestock.PSPriceLevel',$prlevel)

         ->like("CONCAT(' ',product.ProductCode,product.Prd_Description,product.BarCode)", $query ,'left')
         ->limit(50)->get();
        // echo var_dump($query1);die;
        

        if ($query1->num_rows() > 0) {
            foreach ($query1->result_array() as $row) {
                $new_row['label'] = htmlentities(stripslashes($row['Prd_Description']." = Rs.".$row['Price']));
                $new_row['value'] = htmlentities(stripslashes($row['ProductCode']));
                $new_row['costPrice'] = htmlentities(stripslashes($row['Price']));
                $row_set[] = $new_row; 
            }
            echo json_encode($row_set); 
        }
       
    }


     public function loadproductbypcode($product) {
        return $this->db->select('product.*,productcondition.*,productprice.ProductPrice')->from('product')
                        ->where('product.ProductCode', $product)
                        // ->where('productprice.PL_No', $pl)
                        ->join('productcondition', 'productcondition.ProductCode = product.ProductCode')
                        ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                        ->get()->row();
    }


     public function loadproductstockbyid($product,$fromloc)
    {
        return $this->db->select('Stock')
            ->from('productstock')->where('ProductCode', $product)->where('Location', $fromloc)
            ->get()->row();
    }

    public function loadproductstockbyprice($product,$fromloc,$costPrice)
    {

        return $this->db->select('Stock,Price,UnitCost')
            ->from('pricestock')->where('PSCode', $product)->where('PSLocation', $fromloc)->where('Price',$costPrice)
            ->get()->row();
    }
    


      public function loadproductbyserialArrayByCode($product, $pl, $location) {
   
        $query2 = $this->db->select('productserialstock.SerialNo')->from('product')
                ->where('product.ProductCode', $product)
                ->where('productserialstock.Location', $location)
                ->where('productprice.PL_No', $pl)
        ->where('productserialstock.Quantity', 1)
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                ->get();
        $query4 = $this->db->select('productserialstock.SerialNo')->from('product')
                //->where('productserialstock.SerialNo', $product)
                ->where('productserialstock.Location', $location)
                ->where('productprice.PL_No', $pl)
        ->where('productserialstock.Quantity', 1)
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                ->get();
        $query1 = $this->db->select('productcondition.IsSerial')->from('product')
                ->where('product.ProductCode', $product)
                ->where('productcondition.IsSerial', 1)
                ->join('productcondition', 'productcondition.ProductCode = product.ProductCode')
                ->get();
        $query3 = $this->db->select('productcondition.IsSerial')->from('product')
                ->where('productserialstock.SerialNo', $product)
                ->where('productcondition.IsSerial', 1)
                ->join('productcondition', 'productcondition.ProductCode = product.ProductCode')
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->get();
        
        $query5 = $this->db->select('product.ProductCode')->from('product')
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->where('productserialstock.SerialNo', $product)
                // ->group_by('product.ProductCode')
                ->get();
     
        if (($query1->num_rows()) > 0) {
            if (($query2->num_rows()) > 0) {
                foreach ($query2->result_array() as $row) {
                    $row_set[] = htmlentities(stripslashes($row['SerialNo']));
                }
                return ($row_set);
            }
        }else if (($query3->num_rows()) > 0) {
            if (($query5->num_rows()) > 0) {
                //get product code by serial
                foreach ($query5->result_array() as $row) {
                    $pro = htmlentities(stripslashes($row['ProductCode']));
                }
                
                 $query4 = $this->db->select('productserialstock.SerialNo')->from('product')
                ->where('product.ProductCode', $pro)
                ->where('productserialstock.Location', $location)
                ->where('productprice.PL_No', $pl)
                ->where('productserialstock.Quantity', 1)
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                ->get();
                
                foreach ($query4->result_array() as $row) {
                    $row_set[] = htmlentities(stripslashes($row['SerialNo']));
                }
                return ($row_set);
            } 
        } else  {
            return NULL;
        }
    }


     public function newsaveStockOut($grnHed,$post,$grnNo) { 
      
        $product_codeArr = json_decode($_POST['product_code']);
   
        $unitArr = json_decode($_POST['unit_type']);

        $serial_noArr = json_decode($_POST['serial_no']);
        $qtyArr = json_decode($_POST['qty']);
        $sell_priceArr = json_decode($_POST['unit_price']);
        $cost_priceArr = json_decode($_POST['cost_price']);
        $upcArr = json_decode($_POST['upc']);
        //$price_levelArr = json_decode($post['price_level']);
        $price_levelArr = 1;
        $totalAmountArr = json_decode($_POST['total_net']);
        $isSerialArr = json_decode($_POST['isSerial']);
         $location_to = $_POST['toloc'];
        $location_from = $_POST['fromloc'];
        $grnDattime = date("Y-m-d H:i:s");
        
        
        
        
       
            $this->db->trans_start();
             for ($i = 0; $i < count($product_codeArr); $i++) {
                
               

                $grnDtl = array(
                    'TrnsNo' => $grnNo,
                    'Location' => 1,
                    'TrnsDate' => $grnDattime,
                    'FromLocation' => $location_from,
                    'ToLocation' => $location_to,
                    'ProductCode' => $product_codeArr[$i],
                    'UnitPerCase' => $upcArr[$i],
                    'CaseOrUnit' => $unitArr[$i],
                    'TransQty' => $qtyArr[$i],
                    'DismissQty' => 0,
                    'CostPrice' => $cost_priceArr[$i],
                    'PriceLevel' => 1,
                    'SellingPrice' => $sell_priceArr[$i],
                    'TransAmount' => $totalAmountArr[$i],
                    'IsSerial' => $isSerialArr[$i],
                    'Serial' => $serial_noArr[$i]
                );
                $this->db->insert('newstocktransferdtl', $grnDtl);
          
                //update price and product stock
            $this->db->query("CALL SPP_UPDATE_PRICE_STOCK('$product_codeArr[$i]','$qtyArr[$i]','$price_levelArr[$i]','$cost_priceArr[$i]','$sell_priceArr[$i]','$location_from','$serial_noArr[$i]',0,0,0)");
             //update serial stock
             $this->db->query("UPDATE productserialstock AS S
                                INNER JOIN  newstocktransferdtl AS D ON S.ProductCode=D.ProductCode
                                SET S.Quantity=0
                                WHERE S.SerialNo = D.Serial AND D.IsSerial = 1 AND D.TrnsNo = '$grnNo' AND D.Location = '$location_from'");
            }
        
        
        
        
        $this->db->insert('newstocktransferhed', $grnHed);
        $this->update_max_code('Stock Transfer Out');
        $this->db->trans_complete();
       return $this->db->trans_status();
       
    }
    
    public function get_max_code($form)
    {
        
        $query = $this->db->select('*')->where('FormName',$form)->get('codegenerate');
        foreach ($query->result_array() as $row) {
           $code= $row['CodeLimit'];
           $input = $row['AutoNumber'];
           $string= $row['FormCode'];
           $code_len = $row['FCLength'];
           $item_ref = $string . str_pad(($input+1), $code_len, $code, STR_PAD_LEFT);
        } 
        return $item_ref;
    }

    public function update_max_code($form)
    {
        $query = $this->db->select('*')->where('FormName',$form)->get('codegenerate');
        foreach ($query->result_array() as $row) {
           $input = $row['AutoNumber'];
        } 
        $this->db->update('codegenerate',array('AutoNumber'=>($input+1)),array('FormName'=>($form)));
    }

    public function loadStockTransoutHead($transNo){
          return $this->db->select(' newstocktransferhed.*,
            loc_from.location AS from_location_name,
            loc_to.location AS to_location_name')
            ->from('newstocktransferhed')
            ->join('location AS loc_from', 'loc_from.location_id = newstocktransferhed.FromLocation', 'left')
            ->join('location AS loc_to', 'loc_to.location_id = newstocktransferhed.ToLocation', 'left')
            ->like('newstocktransferhed.TrnsNo', $transNo)
            ->get()->row();
    }

    public function loadStockTransoutHeadDetls($transNo){
            return $this->db->select('newstocktransferhed.TrnsNo,newstocktransferdtl.*,product.Prd_Description')
            ->from('newstocktransferhed')
            ->join('newstocktransferdtl','newstocktransferdtl.TrnsNo =newstocktransferhed.TrnsNo','left')
            ->join('product','product.ProductCode =newstocktransferdtl.ProductCode')
             ->like('newstocktransferdtl.TrnsNo', $transNo)
            ->get()->result();
    }


     public function newsaveStockIn($location_to,$location_from,$canDate,$grnNo,$remark,$user) {
        $this->db->trans_start();
        $dattime = date("Y-m-d H:i:s");
       $this->db->update('newstocktransferhed',array('TransIsInProcess'=>0,'TransInDate'=>$dattime,'TransInUser'=>$user,'TransInRemark'=>$remark),array('TrnsNo'=>$grnNo,'FromLocation'=>$location_from,'ToLocation'=>$location_to));
        $query = $this->db->get_where('newstocktransferdtl',array('TrnsNo'=>$grnNo));
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $product_codeArr=$row['ProductCode'];
                $qtyArr=$row['TransQty'];
                $price_levelArr=$row['PriceLevel'];
                $cost_priceArr=$row['CostPrice'];
                $sell_priceArr=$row['SellingPrice'];
                $serial_noArr=$row['Serial'];
                $freeQtyArr=0;
                $isSerialArr=$row['IsSerial'];
                
                //update stock trans dtl
                 $this->db->update('newstocktransferdtl',array('DismissQty'=>$qtyArr),array('ProductCode'=> $product_codeArr,'TrnsNo'=>$serial_noArr,'FromLocation'=> $location_from,'ToLocation'=>$location_to));
                 
                //update to location serial stock 
                $ps = $this->db->select('ProductCode')->from('productserialstock')->where(array('ProductCode'=> $product_codeArr,'SerialNo'=>$serial_noArr,'Location'=>$location_to))->get();
                if($ps->num_rows()>0){
                    $isPro = $this->db->select('InvProductCode')->from('invoicedtl')->where(array('InvProductCode'=> $product_codeArr,'InvSerialNo'=>$serial_noArr,'InvLocation'=>$location_to))->get();
                    if($isPro->num_rows()==0){
                        $this->db->update('productserialstock',array('Quantity'=>1),array('ProductCode'=> $product_codeArr,'SerialNo'=>$serial_noArr,'Location'=> $location_to));
                    }
                }else{
                    if($isSerialArr==1){
                        $this->db->insert('productserialstock', array('ProductCode'=> $product_codeArr,'Location'=> $location_to,'SerialNo'=>$serial_noArr,'Quantity'=>1,'GrnNo'=>$grnNo));
                    }
                }
                

                //update price stock
               $this->db->query("CALL SPT_UPDATE_PRICE_STOCK('$product_codeArr','$qtyArr','$price_levelArr','$cost_priceArr','$sell_priceArr','$location_to')");

            //update product stock
            $this->db->query("CALL SPT_UPDATE_PRO_STOCK('$product_codeArr','$qtyArr',0,'$location_to')");
            }
        }
        
//        $this->update_max_code('CancelGRN');
        $this->db->trans_complete();
       return $this->db->trans_status();
    }



      public function newStockCancel($location_to,$location_from,$canDate,$grnNo,$user,$invCanel) {
        $this->db->trans_start();
        $dattime = date("Y-m-d H:i:s");
       $this->db->update('newstocktransferhed',array('TransIsInProcess'=>0,'TransInDate'=>$dattime,'TransInUser'=>$user,'TransInRemark'=>$remark,'IsCancel'=>1),array('TrnsNo'=>$grnNo,'FromLocation'=>$location_from,'ToLocation'=>$location_to));
        $query = $this->db->get_where('newstocktransferdtl',array('TrnsNo'=>$grnNo));
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $product_codeArr=$row['ProductCode'];
                $qtyArr=$row['TransQty'];
                $price_levelArr=$row['PriceLevel'];
                $cost_priceArr=$row['CostPrice'];
                $sell_priceArr=$row['SellingPrice'];
                $serial_noArr=$row['Serial'];
                $freeQtyArr=0;
                $isSerialArr=$row['IsSerial'];
                
                //update stock trans dtl
                 $this->db->update('newstocktransferdtl',array('DismissQty'=>0),array('ProductCode'=> $product_codeArr,'TrnsNo'=>$serial_noArr,'FromLocation'=> $location_from,'ToLocation'=>$location_to));
                 
                //update to location serial stock 
                $ps = $this->db->select('ProductCode')->from('productserialstock')->where(array('ProductCode'=> $product_codeArr,'SerialNo'=>$serial_noArr,'Location'=>$location_from))->get();
                if($ps->num_rows()>0){
                    $isPro = $this->db->select('InvProductCode')->from('invoicedtl')->where(array('InvProductCode'=> $product_codeArr,'InvSerialNo'=>$serial_noArr,'InvLocation'=>$location_from))->get();
                    if($isPro->num_rows()==0){
                        $this->db->update('productserialstock',array('Quantity'=>1),array('ProductCode'=> $product_codeArr,'SerialNo'=>$serial_noArr,'Location'=> $location_from));
                    }
                }else{
                    if($isSerialArr==1){
                        $this->db->insert('productserialstock', array('ProductCode'=> $product_codeArr,'Location'=> $location_from,'SerialNo'=>$serial_noArr,'Quantity'=>1,'GrnNo'=>$grnNo));
                    }
                }
                

                //update price stock
               $this->db->query("CALL SPT_UPDATE_PRICE_STOCK('$product_codeArr','$qtyArr','$price_levelArr','$cost_priceArr','$sell_priceArr','$location_from')");

            //update product stock
            $this->db->query("CALL SPT_UPDATE_PRO_STOCK('$product_codeArr','$qtyArr',0,'$location_from')");
            }
        }
        $this->db->insert('newstockcanceltranser', $invCanel);
       $this->update_max_code('Stock Transfer Cancel');
        $this->db->trans_complete();
       return $this->db->trans_status();
    }


     public function loadproductbyserial($product, $pl, $location) {
        $query2 = $this->db->select('product.*,productcondition.*,productprice.ProductPrice,productserialstock.SerialNo,goodsreceivenotedtl.GRN_UnitCost AS Prd_CostPrice')->from('product')
                        ->where('productserialstock.SerialNo', $product)
                        ->where('productserialstock.Location', $location)
                        ->where('productprice.PL_No', $pl)
                        ->where('productserialstock.Quantity', 1)
                        ->join('productcondition', 'productcondition.ProductCode = product.ProductCode')
                        ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                        ->join('goodsreceivenotedtl', 'goodsreceivenotedtl.SerialNo = productserialstock.SerialNo')
                        ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                        ->get();
        $query1 =$this->db->select('product.*,productcondition.*,productprice.ProductPrice')->from('product')
                        ->where('product.BarCode', $product)
                        ->where('productprice.PL_No', $pl)
                        ->join('productcondition', 'productcondition.ProductCode = product.ProductCode')
                        ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                        ->get();
        if(($query1->num_rows())>0){
            return $query1->row();
        }else if(($query2->num_rows())>0){
            return $query2->row();
        }
    }

     public function loadproductstockbyidForSerial($product,$location)
    {
        return $this->db->select('productserialstock.Quantity AS Stock')
            ->from('productserialstock')->where('SerialNo', $product)->where('Location', $location)
            ->get()->row();
    }


    public function loadproductbyserialArrayBySerial($product, $pl, $location) {
   
        $query2 = $this->db->select('productserialstock.SerialNo')->from('product')
                ->where('product.ProductCode', $product)
                ->where('productserialstock.Location', $location)
                ->where('productprice.PL_No', $pl)
                ->where('productserialstock.Quantity', 1)
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                ->get();
        $query4 = $this->db->select('productserialstock.SerialNo')->from('product')
                ->where('productserialstock.SerialNo', $product)
                ->where('productserialstock.Location', $location)
                ->where('productprice.PL_No', $pl)
                ->where('productserialstock.Quantity', 1)
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->join('productprice', 'productprice.ProductCode = product.ProductCode')
                ->get();
        $query1 = $this->db->select('productcondition.IsSerial')->from('product')
                ->where('product.ProductCode', $product)
                ->where('productcondition.IsSerial', 1)
                ->join('productcondition', 'productcondition.ProductCode = product.ProductCode')
                ->get();
        $query3 = $this->db->select('productcondition.IsSerial')->from('product')
                ->where('productserialstock.SerialNo', $product)
                ->where('productcondition.IsSerial', 1)
                ->join('productcondition', 'productcondition.ProductCode = product.ProductCode')
                ->join('productserialstock', 'productserialstock.ProductCode = product.ProductCode')
                ->get();
        
        if (($query1->num_rows()) > 0) {
            if (($query2->num_rows()) > 0) {
                foreach ($query2->result_array() as $row) {
                    $row_set[] = htmlentities(stripslashes($row['SerialNo']));
                }
                return ($row_set);
            }
        }else if (($query3->num_rows()) > 0) {
            if (($query4->num_rows()) > 0) {
                foreach ($query4->result_array() as $row) {
                    $row_set[] = htmlentities(stripslashes($row['SerialNo']));
                }
                return ($row_set);
            } 
        } else  {
            return NULL;
        }
    }
}