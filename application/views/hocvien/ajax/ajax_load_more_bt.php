<?php

foreach ($hoc_vien_nop_bais as $hoc_vien_nop_bai) {
    //                    if ($bai_tap_hoc_vien['duration'] == $bai_tap_hoc_vien['lectureOrder']) {
    //                        $buoi = "BTCK";
    //                    } else {
    //                        $buoi = "Buổi " . $bai_tap_hoc_vien['lectureOrder'];
    //                    }
    $buoi = "Buổi " . $hoc_vien_nop_bai['lectureOrder'];
    $date = rebuild_date('l, jS F, Y', strtotime($hoc_vien_nop_bai['date']));
    ?>
    <li class="media" id="bt-hocvien-info">

        <div class="media-body">

            <div class="col-xs-8">
                <a href="#" style="float:left;margin-right:2%">
                    <img src="public/template/hocvien/images/user.png" class="media-object"
                         width="40px"/>
                </a>
                <h4 class="media-heading"><?php echo $hoc_vien_nop_bai['fullname'] ?></h4>
                <?php echo $date ?></p>
            </div>
            <div class="col-xs-4">
                                <span
                                    class="badge alert-success"
                                    style="padding: 5px 7px;margin-top:3px">Lớp <?php echo $hoc_vien_nop_bai['gen'] . "." . $hoc_vien_nop_bai['name'] ?></span>
                                <span
                                    class="badge" style="padding: 5px 7px;margin-top:3px"><?php echo $buoi; ?></span>
            </div>
            <div class="x_content links">

                <?php

                foreach ($hoc_vien_nop_bai['baitap'] as $key => $baitap) {

                    if ($key == 0) {

                        ?>

                        <a href="<?php echo base_url($baitap['source']); ?>"
                           data-gallery
                           class="grid-thumbnail-first baiTap"
                           style="background: url('<?php echo base_url($baitap['source']); ?>') 50% 50% no-repeat;background-size:cover">
                        </a>

                        <?php
                    } else {
                        ?>
                        <a href="<?php echo base_url($baitap['source']); ?>"
                           data-gallery
                           class="grid-thumbnail baiTap"
                           style="background: url('<?php echo base_url($baitap['source']); ?>') 50% 50% no-repeat;background-size:cover">


                        </a>
                        <?php
                    }
                }
                ?>
            </div>

        </div>
    </li>
    <?php
}

?>