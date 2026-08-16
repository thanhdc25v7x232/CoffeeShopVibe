<div class="container py-5">
    <h1 class="text-success mb-3">Liên hệ với chúng tôi</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4">
                <form id="contact-form" action="process_contact.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="name" class="form-control" placeholder="Họ tên của bạn" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email của bạn" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tin nhắn</label>
                            <textarea name="message" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa fa-paper-plane"></i> Gửi lời nhắn
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="contact-info bg-white p-4 shadow-sm h-100">
                <h3 class="text-success mb-2"> Thông tin liên hệ</h3>
                <div class="d-flex align-items-center mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold">Địa chỉ</h6>
                        <p class="mb-0"> P.Ninh Kiều, TP. Cần Thơ</p>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold">Email</h6>
                        <p class="mb-0">support@vibe.vn</p>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold">Điện thoại</h6>
                        <p class="mb-0">0901 234 567</p>
                    </div>
                </div>
                <div class="ratio ratio-16x9 mt-3">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.8415184086424!2d105.7684266!3d10.0299337!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDAxJzQ3LjgiTiAxMDXCsDQ2JzA2LjMiRQ!5e0!3m2!1svi!2svn!4v1620000000000" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contact-form');

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            this.reset();
            alert("Cảm ơn bạn đã gửi tin nhắn. Chúng tôi sẽ liên hệ với bạn ngay khi có thể !");
        });
    });
</script>