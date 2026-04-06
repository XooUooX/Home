// 滚动显示动画
document.addEventListener('DOMContentLoaded', function() {
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    var elements = document.querySelectorAll('[data-reveal]');
    elements.forEach(function(el, index) {
        var delay = el.getAttribute('data-delay');
        var value = delay ? parseFloat(delay) : index * 0.04;
        el.style.transitionDelay = value + 's';
        observer.observe(el);
    });
});

// 图片弹窗功能
function openModal(imageSrc, caption) {
    if (!imageSrc) return;
    var modal = document.getElementById('imageModal');
    var modalImg = document.getElementById('modalImage');
    var modalCaption = document.getElementById('modalCaption');
    
    modal.style.display = 'block';
    modalImg.src = imageSrc;
    modalCaption.textContent = caption || '';
    document.body.style.overflow = 'hidden';
}

function closeModal(event) {
    if (event.target.id === 'imageModal') {
        var modal = document.getElementById('imageModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function closeModalByButton(event) {
    event.stopPropagation();
    var modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// ESC键关闭弹窗
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        var modal = document.getElementById('imageModal');
        if (modal.style.display === 'block') {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
});

// 清除URL中的msg参数，防止刷新后重复显示提示
if (window.location.search.includes('msg=success')) {
    var url = new URL(window.location.href);
    url.searchParams.delete('msg');
    window.history.replaceState({}, '', url.toString());
}
