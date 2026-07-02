function toggleFolders(event) {
  event.preventDefault();
  const menu = document.getElementById("folderMenu");
  menu.style.display = (menu.style.display === "flex") ? "none" : "flex";
}