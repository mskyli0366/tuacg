let currentImageIndex = 0; // 当前图片索引
const totalImages = 10000; // 假设总共有10000张图片

function updateBackground() {
    const backgroundDiv = document.getElementById('background');
    const newImage = new Image(); // 创建一个新的图片对象

    // 新图片的URL
    newImage.src = "https://acg.203666.xyz/img?" + new Date().getTime();

    // 当新图片加载完成后再进行淡出和淡入
    newImage.onload = () => {
        backgroundDiv.style.opacity = 0; // 淡出
        backgroundDiv.style.transform = 'scale(1.1)'; // 缩放

        setTimeout(() => {
            backgroundDiv.style.backgroundImage = `url('${newImage.src}')`; // 设置新背景
            backgroundDiv.style.opacity = 1; // 淡入
            backgroundDiv.style.transform = 'scale(1)'; // 恢复缩放
        }, 1000); // 等待1秒（与CSS中的过渡时间相同）
    };
}

setInterval(updateBackground, 8000);
updateBackground();
