<!-- <table style="border-collapse:collapse;width:290px;margin:0;font-family:'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;" border="0">
    <tr style="text-align:left;font-size:19px;font-family:Arial, Helvetica, sans-serif;">
        <td rowspan="5">
            <?php $logo = $company['Logo']; ?>
            <img style="width:100px;" src="<?php echo base_url($avatar_dir . '/' . $logo); ?>" alt="logo">
        </td>
        <td colspan="5" style="font-size:19px;font-family:Arial, Helvetica, sans-serif;">
            <b><?php echo $company['CompanyName']; ?> <?php echo $company['CompanyName2']; ?></b>
        </td>
    </tr>
    <tr style="text-align:left;font-size:12px;font-family: Arial, Helvetica, sans-serif;">
        <td colspan="5"><?php echo $company['AddressLine01'] ?><br><?php echo $company['AddressLine02'] ?><?php echo $company['AddressLine03'] ?></td>
    </tr>
    <tr style="text-align:left;font-size:12px;font-family: Arial, Helvetica, sans-serif;">
        <td style="padding-bottom:1px;" colspan="5"><?php echo $company['LanLineNo'] ?>, <?php echo $company['Fax'] ?> <?php echo $company['MobileNo'] ?></td>
    </tr>

</table> -->


<table style="border-collapse:collapse;width:100%;font-family:'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;" border="0">
    <tr>
        <!-- Left: Logo -->
        <td style="width:120px;vertical-align:top;">
            <?php $logo = $company['Logo']; ?>
            <img style="width:100px;" src="<?php echo base_url($avatar_dir . '/' . $logo); ?>" alt="logo">
        </td>

        <!-- Right: Company Info -->
        <td style="text-align:right;vertical-align:top;">
            <table style="width:100%;border-collapse:collapse;text-align:right;">
                <tr>
                    <td style="font-size:19px;font-family:Arial, Helvetica, sans-serif;font-weight:bold;">
                        <?php echo $company['CompanyName']; ?> <?php echo $company['CompanyName2']; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:12px;font-family:Arial, Helvetica, sans-serif;">
                        <?php echo $company['AddressLine01']; ?><br>
                        <?php echo $company['AddressLine02']; ?> <?php echo $company['AddressLine03']; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:12px;font-family:Arial, Helvetica, sans-serif;">
                        <?php echo $company['LanLineNo']; ?>, <?php echo $company['Fax']; ?>, <?php echo $company['MobileNo']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
