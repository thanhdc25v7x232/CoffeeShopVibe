<div class="container mt-4">

  <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">

    <div class="carousel-indicators">
      <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>

    <div class="carousel-inner">

      <div class="carousel-item active" data-bs-interval="3000">
        <img src="img/baner/baner1.jpg" class="d-block w-100" alt="Banner 1">
      </div>

      <div class="carousel-item" data-bs-interval="3000">
        <img src="img/baner/baner2.jpg" class="d-block w-100" alt="Banner 2">
      </div>

      <div class="carousel-item" data-bs-interval="3000">
        <img src="img/baner/baner3.jpg" class="d-block w-100" alt="Banner 3">
      </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>

    <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>

  </div>
  <h2 class="text-center text-success mt-4">Đã khát, đã ghiền - Thử liền món mới</h2>
  <br>

  <?php
// Hàm helper để render một dòng sản phẩm
function renderProductRow($title, $products, $rowId, $categoryName = '') {
    if (empty($products)) {
        return;
    }

    $hasMoreThanFour = count($products) > 4;
    $displayProducts = array_slice($products, 0, 4);

    // Tính redirect gắn anchor theo rowId
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
    $redirectUrl = $currentUrl . '#section-' . $rowId;
    ?>
    <div class="category-section mt-5 mb-5 position-relative" id="section-<?= $rowId ?>">
        <h3 class="text-success mb-4"><?= htmlspecialchars($title) ?></h3>
        
        <div class="position-relative">
            <div class="row" id="slider-row-<?= $rowId ?>">
                <?php foreach ($displayProducts as $product): ?>
                    <div class="col-6 col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <a href="/san-pham/<?= $product['sp_ma'] ?>" class="text-decoration-none text-dark position-relative">
                                <?php if (!empty($product['km_phantram'])): ?>
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">-<?= $product['km_phantram'] ?>%</span>
                                <?php endif; ?>
                                <img src="<?= product_image_url($product, $categoryName ?: ($product['l_ten'] ?? '')) ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($product['sp_ten']) ?>"
                                     style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='/img/unnamed.png';">

                                <div class="card-body pb-0">
                                    <h5 class="card-title"><?= htmlspecialchars($product['sp_ten']) ?></h5>

                                    <p class="card-text text-danger font-weight-bold mb-0">
                                        <?php if (!empty($product['km_phantram'])): ?>
                                            <span class="text-muted text-decoration-line-through small"><?= number_format($product['sp_gia'], 0, ',', '.') ?> VNĐ</span>
                                            <br>
                                        <?php endif; ?>
                                        <?= number_format($product['gia_hien_thi'], 0, ',', '.') ?> VNĐ
                                    </p>
                                </div>
                            </a>

                            <div class="card-body pt-2">
                                <form method="POST" action="/cart/add">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $product['sp_ma'] ?>">
                                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl) ?>">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($hasMoreThanFour): ?>
                <button class="carousel-control-prev" type="button" onclick="slideProducts('<?= $rowId ?>', 'prev')" id="prev-btn-<?= $rowId ?>">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" onclick="slideProducts('<?= $rowId ?>', 'next')" id="next-btn-<?= $rowId ?>">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>

  <!-- Dòng 1: Sản phẩm mới nhất -->
  <?php renderProductRow('Sản phẩm mới nhất', $latestProducts, 'latest'); ?>

  <!-- Dòng 2-4: Sản phẩm theo loại -->
  <?php foreach ($categoryProducts as $categoryData): ?>
    <?php renderProductRow($categoryData['category']['l_ten'], $categoryData['products'], 'category-' . $categoryData['category']['l_ma'], $categoryData['category']['l_ten']); ?>
  <?php endforeach; ?>

</div>

<style>
  .category-section {
    position: relative;
    margin-bottom: 3rem;
  }

  /* Mũi tên dễ nhìn hơn, tương tự banner nhưng có nền tròn đậm */
  .category-section .carousel-control-prev,
  .category-section .carousel-control-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    opacity: 1;
    background: rgba(25, 135, 84, 0.95);
    /* xanh đậm */
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  }

  .category-section .carousel-control-prev-icon,
  .category-section .carousel-control-next-icon {
    filter: invert(1) brightness(2);
    /* icon trắng nổi bật */
    width: 18px;
    height: 18px;
  }

  .category-section .carousel-control-prev {
    left: -20px;
  }

  .category-section .carousel-control-next {
    right: -20px;
  }

  .category-section .carousel-control-prev:hover,
  .category-section .carousel-control-next:hover {
    background: rgba(25, 135, 84, 1);
  }

  .category-section .carousel-control-prev:disabled,
  .category-section .carousel-control-next:disabled {
    opacity: 0.35;
    cursor: not-allowed;
  }
</style>

<script>
  // Lưu trữ dữ liệu sản phẩm và chỉ số hiện tại cho mỗi dòng
  const productData = {};
  const currentIndex = {};

  // Dữ liệu cho dòng sản phẩm mới nhất
  <?php if (count($latestProducts) > 4): ?>
    productData['latest'] = <?= json_encode(array_map(function ($product) {
        $product['image_url'] = product_image_url($product, $product['l_ten'] ?? '');
        return $product;
    }, $latestProducts)) ?>;
    currentIndex['latest'] = 0;
  <?php endif; ?>

  // Dữ liệu cho các dòng sản phẩm theo loại
  <?php foreach ($categoryProducts as $categoryData): ?>
    <?php if (count($categoryData['products']) > 4): ?>
      productData['category-<?= $categoryData['category']['l_ma'] ?>'] = <?= json_encode(array_map(function ($product) use ($categoryData) {
          $product['image_url'] = product_image_url($product, $categoryData['category']['l_ten'] ?? '');
          return $product;
      }, $categoryData['products'])) ?>;
      currentIndex['category-<?= $categoryData['category']['l_ma'] ?>'] = 0;
    <?php endif; ?>
  <?php endforeach; ?>

  function slideProducts(rowId, direction) {
    if (!productData[rowId]) return;

    const products = productData[rowId];
    const maxIndex = Math.ceil(products.length / 4) - 1;

    if (direction === 'next') {
      currentIndex[rowId] = Math.min(currentIndex[rowId] + 1, maxIndex);
    } else {
      currentIndex[rowId] = Math.max(currentIndex[rowId] - 1, 0);
    }

    const startIdx = currentIndex[rowId] * 4;
    const endIdx = startIdx + 4;
    const displayProducts = products.slice(startIdx, endIdx);

    // Cập nhật HTML
    const row = document.getElementById('slider-row-' + rowId);
    row.innerHTML = '';

    displayProducts.forEach(product => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-3 mb-4';
      const discountBadge = product.km_phantram
        ? `<span class="badge bg-danger position-absolute top-0 end-0 m-2">-${product.km_phantram}%</span>`
        : '';
      const oldPriceLine = product.km_phantram
        ? `<span class="text-muted text-decoration-line-through small">${parseInt(product.sp_gia).toLocaleString('vi-VN')} VNĐ</span><br>`
        : '';
      const displayPrice = product.km_phantram ? product.gia_hien_thi : product.sp_gia;

      col.innerHTML = `
            <div class="card h-100 shadow-sm">
                <a href="/san-pham/${product.sp_ma}" class="text-decoration-none text-dark position-relative">
                    ${discountBadge}
                    <img src="${product.image_url || '/img/unnamed.png'}"
                         class="card-img-top product-thumb"
                         style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
                    <div class="card-body pb-0">
                        <h5 class="card-title product-name"></h5>
                        <p class="card-text text-danger font-weight-bold mb-0">
                            ${oldPriceLine}
                            ${parseInt(displayPrice).toLocaleString('vi-VN')} VNĐ
                        </p>
                    </div>
                </a>
                <div class="card-body pt-2">
                    <form method="POST" action="/cart/add">
                        <input type="hidden" name="_csrf" value="${CSRF_TOKEN}">
                        <input type="hidden" name="id" value="${product.sp_ma}">
                        <input type="hidden" class="redirect-field" name="redirect">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                    </form>
                </div>
            </div>
        `;
      // Gán qua thuộc tính DOM (không phải innerHTML) để tránh DOM XSS khi tên sản phẩm chứa HTML/JS
      col.querySelector('.product-name').textContent = product.sp_ten;
      col.querySelector('.product-thumb').alt = product.sp_ten;
      col.querySelector('.redirect-field').value = window.location.pathname + window.location.search + '#section-' + rowId;
      row.appendChild(col);
    });

    // Cập nhật trạng thái nút
    const prevBtn = document.getElementById('prev-btn-' + rowId);
    const nextBtn = document.getElementById('next-btn-' + rowId);

    if (prevBtn) {
      prevBtn.disabled = currentIndex[rowId] === 0;
    }
    if (nextBtn) {
      nextBtn.disabled = currentIndex[rowId] === maxIndex;
    }
  }

  // Khởi tạo trạng thái ban đầu cho các nút
  document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo cho dòng sản phẩm mới nhất
    <?php if (count($latestProducts) > 4): ?>
      const latestMaxIndex = Math.ceil(productData['latest'].length / 4) - 1;
      const latestPrevBtn = document.getElementById('prev-btn-latest');
      const latestNextBtn = document.getElementById('next-btn-latest');
      if (latestPrevBtn) latestPrevBtn.disabled = true;
      if (latestNextBtn && latestMaxIndex === 0) latestNextBtn.disabled = true;
    <?php endif; ?>

    // Khởi tạo cho các dòng sản phẩm theo loại
    <?php foreach ($categoryProducts as $categoryData): ?>
      <?php if (count($categoryData['products']) > 4): ?>
        const categoryRowId = 'category-<?= $categoryData['category']['l_ma'] ?>';
        const categoryMaxIndex = Math.ceil(productData[categoryRowId].length / 4) - 1;
        const categoryPrevBtn = document.getElementById('prev-btn-' + categoryRowId);
        const categoryNextBtn = document.getElementById('next-btn-' + categoryRowId);
        if (categoryPrevBtn) categoryPrevBtn.disabled = true;
        if (categoryNextBtn && categoryMaxIndex === 0) categoryNextBtn.disabled = true;
      <?php endif; ?>
    <?php endforeach; ?>
  });
</script>

<script>
(function () {
    var initialVersion = null;
    function checkProductsUpdated() {
        fetch('/api/products-version')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (initialVersion === null) {
                    initialVersion = data.version;
                    return;
                }
                if (data.version !== initialVersion) {
                    location.reload();
                }
            })
            .catch(function () { /* bỏ qua lỗi mạng tạm thời */ });
    }
    // Tự phát hiện khi admin vừa thêm/sửa/xóa sản phẩm hoặc khuyến mãi, tự tải lại trang
    setInterval(checkProductsUpdated, 8000);
})();
</script>
