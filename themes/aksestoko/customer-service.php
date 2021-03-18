<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a> 
      
  </h3>
</div>

    <section class="py-main section-cs">
      <div class="container container-md">
        <div class="heading text-center">
          <h2>Layanan Pelanggan</h2>
          <p>Tanyakan keluhan Anda kepada kami. Untuk mendapatkan response yang lebih cepat, Anda bisa gunakan Live Chat dengan menekan tombol obrolan di kanan bawah, aktif pada hari Senin - Jumat pukul 08.00 - 16.00.</p>
        </div>

        <div class="content">
          <form class="needs-validation mt-4" action="<?= base_url(aksestoko_route('aksestoko/home/add_issue_jira'));?>" method="POST">
            <div class="row">
              <div class="col-12 col-md-12">
                <div class="form-group">
                  <label for="validationCustom04">Subject</label>
                  <div class="form-group">
                    <select class="form-control" required="" name="subject">
                      <option value="" disabled="true">Pilih subject...</option>
                      <?php if($this->session->flashdata('value')) { ?>
                        <option value="<?=$this->session->flashdata('value')['subject']?>"><?=$this->session->flashdata('value')['subject']?></option>
                      <?php } ?>
                      <option value="Akun Saya">Akun Saya</option>
                      <option value="Pembayaran">Pembayaran</option>
                      <option value="Pengiriman">Pengiriman</option>
                      <option value="Pesanan">Pesanan</option>
                      <option value="Poin & Loyalty">Poin & Loyalty</option>
                      <option value="Umum">Umum</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-12">
                <div class="form-group">
                  <label for="">Keluhan Anda</label>
                  <textarea type="text" name="description" class="form-control" id="" placeholder="Tuliskan disini..." rows="5" value="" required="" style="resize: none;"><?=$this->session->flashdata('value')['description']?></textarea> 
                </div>
              </div>
            </div>
            <div class="clearfix mt-4">
              <button type="submit" class="btn btn-primary btn-block">Kirim</button>
            </div>
          </form>
        </div>
        
        <?php
          if ($listIssues && count($listIssues) > 0) { ?>
        <div class="row mt-5">
          <div class="col-12 text-center mb-4">
            <h5 class="p-0 m-0">Daftar Keluhan Anda</h5>
            <span class="text-muted">Hanya menampilkan 5 keluhan terbaru</span>
          </div>

          <?php foreach ($listIssues as $key => $issue) { ?>
            <div class="col-md-12 mt-2">
              <div class="box p-box mb-1">
                  <div class="row mb-4">
                      <div class="col-md-12">
                        <h6><?=$issue['subject']?></h6>
                        <p class="mb-2"><?=$issue['description']?></p>
                      </div>
                      <div class="col-md-3">
                          <label class="p-0 m-0">Tipe Tiket</label>
                          <p class="p-0 m-0"><?=$issue['type']?></p>
                      </div>
                      <div class="col-md-3">
                          <label class="p-0 m-0">Kode Tiket</label>
                          <p class="p-0 m-0"><?=$issue['key']?></p>
                      </div>
                      <div class="col-md-3">
                          <label class="p-0 m-0">Status Tiket</label>
                          <p class="p-0 m-0"><?=$issue['status']?></p>
                      </div>
                      <div class="col-md-3">
                          <label class="p-0 m-0">Ditangani oleh</label>
                          <p class="p-0 m-0"><?=$issue['assignee']?></p>
                      </div>
                  </div>
                  <div class="footer-kredit-mandiri" style="padding">
                      <div class="row justify-content-end">
                          <div class="col-12 text-right">
                              <div class="buttonKreditMandiri">
                                  <a href="javascript:void(0)" class="detail-issue" data-id-ticket="<?=$issue['id']?>" data-key-ticket="<?=$issue['key']?>">Lihat Detail<i class="fal fa-angle-right"></i></a>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
            </div>
          <?php } ?>
          
        </div>
          <?php }
        ?>
        
      </div>
    </section>

<!-- Modal Detail -->
<div class="modal fade" id="detailIssue" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center" id="detailIssueLabel">Detail Tiket <span></span></h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-12">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-12">
                    <h6 id="subjectModal"></h6>
                    <p class="mb-2" id="descriptionModal"></p>
                  </div>
                  <div class="col-md-4 mb-2">
                      <label class="p-0 m-0">Tipe Tiket</label>
                      <p class="p-0 m-0" id="typeModal"></p>
                  </div>
                  <div class="col-md-4 mb-2">
                      <label class="p-0 m-0">Kode Tiket</label>
                      <p class="p-0 m-0" id="keyModal"></p>
                  </div>
                  <div class="col-md-4 mb-2">
                      <label class="p-0 m-0">Status Tiket</label>
                      <p class="p-0 m-0" id="statusModal"></p>
                  </div>
                  <div class="col-md-4 mb-2">
                      <label class="p-0 m-0">Ditangani oleh</label>
                      <p class="p-0 m-0" id="assigneeModal"></p>
                  </div>
                  <div class="col-md-4 mb-2">
                      <label class="p-0 m-0">Dibuat pada</label>
                      <p class="p-0 m-0" id="createdModal"></p>
                  </div>
                  <div class="col-md-4 mb-2">
                      <label class="p-0 m-0">Diperbarui pada</label>
                      <p class="p-0 m-0" id="updatedModal"></p>
                  </div>
                  <div class="col-12 mt-3" id="commentModal"></div>
                  <div class="col-12 mt-3" id="addCommentModal">
                    <textarea name="add-comment" id="add-comment" class="form-control mb-2" placeholder="Tuliskan komentar..."></textarea>
                    <button class="btn btn-primary btn-sm float-right" id="btn-comment">Kirim</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  var statusIssue = {
      'To Do' : 'Dalam Antrean',
      'In Progress' : 'Sedang Diproses',
      'Done' : 'Selesai'
  };
  var user = `<?= $this->session->userdata('company_name') . " (" . $this->session->userdata('username') . ")" ?>`.trim();
  $(document).ready(function(){
    $("#btn-comment").click(function (e) {
      e.preventDefault();
      let comment = $("#add-comment").val();
      let issueId = $(this).data('id-ticket')
      if(comment.length > 0) {
        $.ajax({
          url: "<?= base_url(aksestoko_route('aksestoko/home/add_comment_jira/'))?>"+`${issueId}?body=${encodeURIComponent(comment)}`,
          method : "GET",
          dataType : "json",
          success : function(response){
            if(response.status) {
              $("#add-comment").val('')
              alertCustom(response.message, "success", 1060);
              reloadIssue(issueId);
            } else {
              alertCustom(response.message, "danger", 1060);
            }
          },
          error: function(data, e){
            alertCustom("Tidak dapat menyimpan komentar", "danger", 1060);
          }
        })
      }
    })
    
    $('.detail-issue').click(function (e) {
      e.preventDefault();
      $('#detailIssueLabel span').html(`${$(this).data('key-ticket')}`);
      reloadIssue($(this).data('id-ticket'));
    });
  });

  function reloadIssue(issueId) {
    $.ajax({
      url: "<?= base_url(aksestoko_route('aksestoko/home/getJiraIssue/'))?>"+issueId,
      method : "GET",
      dataType : "json",
      success : function(response){
        if(response.status) {
          $("#subjectModal").html(response.fields.summary);
          $("#descriptionModal").html(response.fields.description);
          $("#typeModal").html(response.fields.issuetype.name);
          $("#keyModal").html(response.key);
          $("#statusModal").html(statusIssue[response.fields.status.name]);
          $("#assigneeModal").html(response.fields.assignee.displayName);
          $("#createdModal").html(moment(response.fields.created).format('DD MMM YYYY HH:mm'));
          $("#updatedModal").html(moment(response.fields.updated).format('DD MMM YYYY HH:mm'));
          let commentHtml = '<h5>Komentar</h5>';
          for (const comment of response.fields.comment.comments) {
            let commentSplit = comment.body.split("|||||");
            let author = commentSplit.length > 1 ? commentSplit[0] : comment.author.displayName;
            let body = commentSplit.length > 1 ? commentSplit[1] : comment.body;
            let isUser = user === author.trim();
            commentHtml += `
              <div class="box p-box p-2 px-3 mb-2" ${isUser ? 'style="background-color: #888888;border-color: #888888;color: white;"' : ''}>
                <div class="row">
                  <div class="col-12">
                    <p class="p-0 m-0">${body.trim()}</p>
                  </div>
                  <div class="col-12">
                    <small class="${isUser ? '' : 'text-muted'}">Oleh ${author.trim()} pada ${moment(comment.created).format('DD MMM YYYY HH:mm')}</small>
                  </div>
                </div>
              </div>
            `;
          }
          let commentClosed = `
            <div class="alert alert-info " role="alert">
              <p style="font-size: 13px;"><i class="fa fa-info-circle"></i> Tidak dapat menambahkan komentar, status tiket telah selesai.</p>
            </div>
          `;

          if(response.fields.status.name === 'Done') $("#addCommentModal").html(commentClosed);
          $("#commentModal").html(commentHtml);
          $("#btn-comment").attr('data-id-ticket', issueId)
          $("#detailIssue").modal('show');
        } else {
          alertCustom("Tidak dapat mendapatkan data", "danger");
        }
      },
      error: function(data, e){
        alertCustom("Tidak dapat menampilkan data", "danger");
      }
    })
  }

  (function(w,d,u){
          var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
          var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
  })(window,document,'https://cdn.bitrix24.id/b11907515/crm/site_button/loader_3_wclev2.js');
</script>