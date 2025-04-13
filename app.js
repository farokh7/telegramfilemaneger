let currentFolder = null;

function loadFolder(folderId = null) {
  currentFolder = folderId;
  fetch('get_folder.php?folder_id=' + (folderId ?? ''))
    .then(res => res.json())
    .then(data => {
      document.getElementById('breadcrumb').innerText = data.breadcrumb;
      const content = document.getElementById('content');
      content.innerHTML = '';

      data.folders.forEach(f => {
        const div = document.createElement('div');
        div.innerHTML = '📁 ' + f.name;
        div.onclick = () => loadFolder(f.id);
        content.appendChild(div);
      });

      data.files.forEach(file => {
        const div = document.createElement('div');
        div.innerHTML = `<a href="${file.telegram_url}" target="_blank">📄 ${file.name}</a>`;
        content.appendChild(div);
      });
    });
}

function newFolder() {
  showModal('ایجاد پوشه جدید', false);
}

function newFile() {
  showModal('افزودن فایل جدید', true);
}

function showModal(title, isFile) {
  document.getElementById('modal-title').innerText = title;
  document.getElementById('input-name').value = '';
  document.getElementById('input-url').value = '';
  document.getElementById('input-url').style.display = isFile ? 'block' : 'none';
  document.getElementById('modal').classList.remove('hidden');
  document.getElementById('modal').dataset.type = isFile ? 'file' : 'folder';
}

function closeModal() {
  document.getElementById('modal').classList.add('hidden');
}

function submitModal() {
  const type = document.getElementById('modal').dataset.type;
  const name = document.getElementById('input-name').value;
  const url = document.getElementById('input-url').value;

  const data = new FormData();
  data.append('name', name);
  data.append('folder_id', currentFolder ?? '');

  if (type === 'file') data.append('telegram_url', url);

  fetch(type === 'file' ? 'add_file.php' : 'add_folder.php', {
    method: 'POST',
    body: data
  }).then(() => {
    closeModal();
    loadFolder(currentFolder);
  });
}

loadFolder();
