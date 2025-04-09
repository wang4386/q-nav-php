// 全局变量
let currentPage = 1;
const itemsPerPage = 8;
let currentCategory = "all";
let links = [];
let categories = [];
let settings = {};

// DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    // 如果是前台页面
    if (document.querySelector('.frontend')) {
        initFrontend();
    }
    
    // 如果是后台页面
    if (document.querySelector('.admin-panel')) {
        initAdmin();
    }
});

// 前台初始化
function initFrontend() {
    loadData().then(() => {
        renderFrontend();
    }).catch(error => {
        console.error('初始化失败:', error);
        alert('加载数据失败，请刷新页面或检查网络连接！');
    });
}

// 后台初始化
function initAdmin() {
    // 后台页面已经通过PHP渲染了初始数据
    // 这里只需要绑定事件
    bindAdminEvents();
}

// 加载数据
async function loadData() {
    try {
        const [linksRes, categoriesRes, settingsRes] = await Promise.all([
            fetch('/api.php?action=get-links'),
            fetch('/api.php?action=get-categories'),
            fetch('/api.php?action=get-settings')
        ]);
        
        if (!linksRes.ok || !categoriesRes.ok || !settingsRes.ok) {
            throw new Error('获取数据失败');
        }
        
        links = await linksRes.json();
        categories = await categoriesRes.json();
        settings = await settingsRes.json();
        
        return {
            links: links.data,
            categories: categories.data,
            settings: settings.data
        };
    } catch (error) {
        console.error('加载数据失败:', error);
        throw error;
    }
}

// 渲染前台
function renderFrontend() {
    const container = document.getElementById('nav-links');
    const filteredLinks = currentCategory === "all" 
        ? links 
        : links.filter(link => link.category_name === currentCategory);

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedLinks = filteredLinks.slice(startIndex, endIndex);

    container.innerHTML = paginatedLinks.map(link => {
        return `
            <li class="nav-item">
                <a href="${escapeHtml(link.url)}" class="nav-link" target="_blank">
                    ${link.logo ? `<img src="${escapeHtml(link.logo)}" class="link-logo" alt="${escapeHtml(link.name)}">` : ''}
                    <div class="link-info">
                        <div class="link-name">${escapeHtml(link.name)}</div>
                        <div class="link-desc">${escapeHtml(link.description)}</div>
                    </div>
                </a>
                <span class="status-badge ${link.status === 'normal' ? 'status-normal' : 'status-error'}">
                    ${link.status === 'normal' ? '正常' : '维护'}
                </span>
            </li>
        `;
    }).join('');

    updateCategoryFilters();
    updatePaginationButtons();
}

// 更新分类过滤器
function updateCategoryFilters() {
    const container = document.getElementById('category-filters');
    if (!container) return;

    container.innerHTML = `
        <button class="category-btn ${currentCategory === 'all' ? 'active' : ''}" data-category="all">全部 (${links.length})</button>
    `;

    categories.forEach(cat => {
        const count = links.filter(link => link.category_name === cat.name).length;
        container.innerHTML += `
            <button class="category-btn ${currentCategory === cat.name ? 'active' : ''}" data-category="${cat.name}">
                ${cat.name} (${count})
            </button>
        `;
    });

    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.dataset.category;
            currentPage = 1;
            renderFrontend();
        });
    });
}

// 更新分页按钮状态
function updatePaginationButtons() {
    const filteredLinks = currentCategory === "all" 
        ? links 
        : links.filter(link => link.category_name === currentCategory);
    
    const totalPages = Math.ceil(filteredLinks.length / itemsPerPage);
    
    if (document.getElementById('prev-page')) {
        document.getElementById('prev-page').disabled = currentPage === 1;
        document.getElementById('next-page').disabled = currentPage >= totalPages;
    }
    
    if (document.getElementById('admin-prev-page')) {
        document.getElementById('admin-prev-page').disabled = currentPage === 1;
        document.getElementById('admin-next-page').disabled = currentPage >= totalPages;
    }
}

// 上一页
function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        renderFrontend();
    }
}

// 下一页
function nextPage() {
    const filteredLinks = currentCategory === "all" 
        ? links 
        : links.filter(link => link.category_name === currentCategory);
    
    if (currentPage * itemsPerPage < filteredLinks.length) {
        currentPage++;
        renderFrontend();
    }
}

// 后台功能
function bindAdminEvents() {
    // 保存网站设置
    window.saveWebsiteSettings = async function() {
        const websiteLogo = document.getElementById('website-logo-input').value;
        const websiteTitle = document.getElementById('website-title-input').value;
        
        try {
            const response = await fetch('/api.php?action=update-settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    website_logo: websiteLogo,
                    website_title: websiteTitle
                })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('设置保存成功');
                window.location.reload();
            } else {
                alert('保存失败');
            }
        } catch (error) {
            console.error('保存设置失败:', error);
            alert('保存失败，请重试');
        }
    };
    
    // 保存页脚信息
    window.saveFooterInfo = async function() {
        const footerInfo = document.getElementById('footer-text').value;
        
        try {
            const response = await fetch('/api.php?action=update-settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    footer_info: '© 青柠 ' + footerInfo
                })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('页脚信息保存成功');
                window.location.reload();
            } else {
                alert('保存失败');
            }
        } catch (error) {
            console.error('保存页脚信息失败:', error);
            alert('保存失败，请重试');
        }
    };
    
    // 添加新分类
    window.addNewCategory = async function() {
        const name = document.getElementById('new-category').value.trim();
        if (!name) {
            alert('分类名称不能为空');
            return;
        }
        
        try {
            const response = await fetch('/api.php?action=add-category', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('分类添加成功');
                window.location.reload();
            } else {
                alert('添加失败');
            }
        } catch (error) {
            console.error('添加分类失败:', error);
            alert('添加失败，请重试');
        }
    };
    
    // 删除分类
    window.deleteCategory = async function(id) {
        if (!confirm('确认删除该分类？此操作不会删除链接，只会将链接分类重置为第一个分类')) {
            return;
        }
        
        try {
            const response = await fetch('/api.php?action=delete-category', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('分类删除成功');
                window.location.reload();
            } else {
                alert('删除失败');
            }
        } catch (error) {
            console.error('删除分类失败:', error);
            alert('删除失败，请重试');
        }
    };
    
    // 添加新链接
    window.addNewLink = async function() {
        try {
            const response = await fetch('/api.php?action=add-link', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: '新链接',
                    url: 'https://',
                    category_id: categories[0].id,
                    description: '',
                    status: 'normal',
                    logo: ''
                })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('链接添加成功');
                window.location.reload();
            } else {
                alert('添加失败');
            }
        } catch (error) {
            console.error('添加链接失败:', error);
            alert('添加失败，请重试');
        }
    };
    
    // 保存链接
    window.saveLink = async function(row) {
        const id = row.dataset.id;
        const name = row.querySelector('.name-input').value.trim();
        const url = row.querySelector('.url-input').value.trim();
        const categoryId = row.querySelector('.category-select').value;
        const description = row.querySelector('.description-textarea').value.trim();
        const status = row.querySelector('.status-select').value;
        const logo = row.querySelector('.logo-input').value.trim();
        
        if (!name || !url || !isValidUrl(url)) {
            alert('名称和有效URL是必填项');
            return;
        }
        
        try {
            const response = await fetch('/api.php?action=update-link', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    name,
                    url,
                    category_id: categoryId,
                    description,
                    status,
                    logo
                })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('链接保存成功');
            } else {
                alert('保存失败');
            }
        } catch (error) {
            console.error('保存链接失败:', error);
            alert('保存失败，请重试');
        }
    };
    
    // 删除链接
    window.deleteLink = async function(row) {
        if (!confirm('确认删除该链接？')) {
            return;
        }
        
        const id = row.dataset.id;
        
        try {
            const response = await fetch('/api.php?action=delete-link', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('链接删除成功');
                row.remove();
            } else {
                alert('删除失败');
            }
        } catch (error) {
            console.error('删除链接失败:', error);
            alert('删除失败，请重试');
        }
    };
    
    // 移动链接位置
    window.moveUp = function(row) {
        const prev = row.previousElementSibling;
        if (prev) {
            row.parentNode.insertBefore(row, prev);
        }
    };
    
    window.moveDown = function(row) {
        const next = row.nextElementSibling;
        if (next) {
            row.parentNode.insertBefore(next, row);
        }
    };
    
    // 保存所有链接
    window.saveAllLinks = async function() {
        const rows = document.querySelectorAll('#links-list tr');
        const order = Array.from(rows).map(row => row.dataset.id);
        
        try {
            const response = await fetch('/api.php?action=update-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ order })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('排序保存成功');
            } else {
                alert('保存失败');
            }
        } catch (error) {
            console.error('保存排序失败:', error);
            alert('保存失败，请重试');
        }
    };
    
    // 导出链接
    window.exportLinks = function() {
        const data = JSON.stringify(links, null, 2);
        const blob = new Blob([data], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'links_export.json';
        a.click();
        URL.revokeObjectURL(url);
    };
    
    // 导入链接
    window.importLinks = function(file) {
        if (!file || file.type !== 'application/json') {
            alert('请选择有效的JSON文件');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = async function(e) {
            try {
                const importedLinks = JSON.parse(e.target.result);
                if (!Array.isArray(importedLinks)) {
                    throw new Error('导入数据格式不正确');
                }
                
                // 验证数据
                for (const link of importedLinks) {
                    if (!link.name || !link.url || !link.category_id) {
                        throw new Error('导入数据格式不正确');
                    }
                }
                
                // 发送到服务器
                const response = await fetch('/api.php?action=update-links', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(importedLinks)
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('链接导入成功');
                    window.location.reload();
                } else {
                    alert('导入失败');
                }
            } catch (error) {
                console.error('导入失败:', error);
                alert('导入失败，请检查文件格式是否正确');
            }
        };
        reader.readAsText(file);
    };
    
    // 修改密码
    window.changePassword = async function() {
        const oldPassword = document.getElementById('old-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        if (!oldPassword || !newPassword || !confirmPassword) {
            alert('所有字段不能为空');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            alert('新密码和确认密码不一致');
            return;
        }
        
        if (newPassword.length < 6) {
            alert('新密码至少需要6位');
            return;
        }
        
        try {
            const response = await fetch('/api.php?action=change-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    old_password: oldPassword,
                    new_password: newPassword
                })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('密码修改成功');
                document.getElementById('old-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            } else {
                alert(result.message || '修改失败');
            }
        } catch (error) {
            console.error('修改密码失败:', error);
            alert('修改失败，请重试');
        }
    };
    
    // 退出登录
    window.logout = async function() {
        try {
            const response = await fetch('/api.php?action=logout');
            const result = await response.json();
            if (result.success) {
                window.location.href = '/admin.php';
            }
        } catch (error) {
            console.error('退出登录失败:', error);
        }
    };
}

// 辅助函数
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}
