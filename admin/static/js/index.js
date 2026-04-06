// 清除URL中的msg参数，防止刷新后重复显示提示
if (window.location.search.includes('msg=')) {
    var url = new URL(window.location.href);
    url.searchParams.delete('msg');
    window.history.replaceState({}, '', url.toString());
}
