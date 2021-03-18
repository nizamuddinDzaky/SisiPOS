
    <!-- Start Status area -->
    <div class="notika-status-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30">
                        <div class="website-traffic-ctn">
                            <!-- <h2><span class="counter">100</span></h2> -->
                            <h2><span style="color: #00c292;"><?= ($user_basic) ? $user_basic->id : 0 ?></span></h2>
                            <p>User type Basic</p>
                        </div>
                        <div class="sparkline-bar-stats1"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30">
                        <div class="website-traffic-ctn">
                            <h2><span style="color: #00c292;"><?= ($user_free) ? $user_free->id : 0 ?></span></h2>
                            <p>User type Free</p>
                        </div>
                        <div class="sparkline-bar-stats2"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30 dk-res-mg-t-30">
                        <div class="website-traffic-ctn">
                            <h2><span style="color: #00c292;">Rp. <?= ($payment_basic) ? $this->sma->formatMoney($payment_basic->amount) : 0 ?></span></h2>
                            <p>Jumlah Pembayaran Plan (Basic)</p>
                        </div>
                        <div class="sparkline-bar-stats3"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30 dk-res-mg-t-30">
                        <div class="website-traffic-ctn">
                            <h2><span style="color: #00c292;">Rp. <?= ($payment_addon) ? $this->sma->formatMoney($payment_addon->subtotal) : 0 ?></span></h2>
                            <p>Jumlah Pembayaran Add-on</p>
                        </div>
                        <div class="sparkline-bar-stats4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div><br>
    <!-- End Status area-->
    <!-- Start Sale Statistic area-->
    <!-- <div class="sale-statistic-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="sale-statistic-inner notika-shadow mg-tb-30">
                        <div class="curved-inner-pro">
                            <div class="curved-ctn">
                                <h2>Tagihan vs Pembayaran</h2>
                                <p>Grafik perbandingan tagihan dengan pembayaran</p>
                            </div>
                        </div>
                        <div id="curved-line-chart" class="flot-chart-sts flot-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <!-- End Sale Statistic area-->
    <!-- Start Email Statistic area-->
    <div class="notika-email-post-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <div class="email-statis-inner notika-shadow">
                        <div class="email-ctn-round">
                            <div class="email-rdn-hd">
								<h2>Statistics</h2>
							</div>
                            <div class="email-round-gp">
                                <div class="email-round-pro">
                                    <div class="email-signle-gp">
                                        <input type="text" class="knob" value="0" data-rel="<?= $percent_free ?>" data-linecap="round" data-width="90" data-bgcolor="#E4E4E4" data-fgcolor="#00c292" data-thickness=".10" data-readonly="true" disabled>
                                    </div>
                                    <div class="email-ctn-nock">
                                        <p>Free</p>
                                    </div>
                                </div>
                                <div class="email-round-pro">
                                    <div class="email-signle-gp">
                                        <input type="text" class="knob" value="0" data-rel="<?= $percent_basic ?>" data-linecap="round" data-width="90" data-bgcolor="#E4E4E4" data-fgcolor="#00c292" data-thickness=".10" data-readonly="true" disabled>
                                    </div>
                                    <div class="email-ctn-nock">
                                        <p>Basic</p>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <div class="recent-post-wrapper notika-shadow sm-res-mg-t-30 tb-res-ds-n dk-res-ds">
                        <div class="recent-post-ctn">
                            <div class="recent-post-title">
                                <h2>Jatuh Tempo</h2>
                            </div>
                        </div>
                        <div class="recent-post-items">
                            <div class="recent-post-signle rct-pt-mg-wp">
                                <?php 
                                    if($user_basic_all){
                                        foreach ($user_basic_all as $key => $v) {
                                ?>
                                <div class="recent-post-flex">
                                    <div class="recent-post-it-ctn">
                                        <h2><?= $v->company ?></h2>
                                        <p><?=  $this->sma->hrsd($v->expired_date) ?> </p>
                                    </div>
                                </div>
                                <br>
                                <?php 
                                        }
                                    }else{
                                ?>
                                    <div class="recent-post-flex">
                                        <div class="recent-post-it-ctn">
                                            <h2>Tidak Ada Data</h2>
                                        </div>
                                    </div>
                                <?php
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <div class="recent-items-wp notika-shadow sm-res-mg-t-30">
                        <div class="rc-it-ltd">
                            <div class="recent-items-ctn">
                                <div class="recent-items-title">
                                    <h2>Pembayaran Baru</h2>
                                </div>
                            </div>
                            <div class="recent-items-inn">
                                <table class="table table-inner table-vmiddle">
                                
                                    <thead>
                                        <tr>
                                            <th>Nama Distributor</th>
                                            <th>Jumlah Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                        if($new_payment){
                                            foreach ($new_payment as $key => $value) {
                                    ?>
                                        <tr>
                                            <td><?= $value->company_name ?></td>
                                            <td class="f-500 c-cyan">Rp. <?= $this->sma->formatMoney($value->total) ?></td>
                                        </tr>
                                    <?php 
                                            }
                                        }else{
                                    ?>
                                        <tr>
                                            <td colspan="2" style="text-align: center;">Tidak Ada Data</td>
                                        </tr>
                                    <?php
                                        } 
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- <div class="recent-post-signle">
                                <a href="#">
                                    <div class="recent-post-flex rc-ps-vw">
                                        <div class="recent-post-line rct-pt-mg">
                                            <a href="<?= site_url('billing_portal/laporan') ?>"><p>View All</p></a>
                                        </div>
                                    </div>
                                </a>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Email Statistic area-->
