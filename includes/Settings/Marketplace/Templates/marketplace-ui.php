        <div class="wrap" id="t9-marketplace">
            <h1>Marketplace</h1>
            <div style="display: flex;">
                <div style="width: 190px; padding-right: 20px; border-right: 1px solid #ddd;">
                    <ul style="list-style: none; padding-left: 0;">
                        <li><a href="#" class="t9-tab active" data-tab="templates">Templates</a></li>
                        <li><a href="#" class="t9-tab" data-tab="modules">Modules</a></li>
                        <li><a href="#" class="t9-tab" data-tab="addons">Addons</a></li>
                    </ul>
                </div>
                <div style="flex-grow: 1; padding-left: 20px;">
                    <div id="t9-content-area"><p>Loading...</p></div>
                </div>
            </div>

            <div id="t9-license-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
                <div style="background:white; padding:30px; max-width:400px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.3);">
                    <h2>Enter License Key</h2>
                    <input type="text" id="t9-license-key" style="width:100%; padding:10px; margin-top:10px;">
                    <button id="t9-submit-license" class="button button-primary" style="margin-top:10px;">Confirm & Download</button>
                    <button id="t9-close-modal" class="button" style="margin-top:10px;">Cancel</button>
                    <div id="t9-download-status" style="margin-top:10px; font-style: italic; color: #555;"></div>
                </div>
            </div>

            <script>
            document.addEventListener("DOMContentLoaded", function () {
                function loadMarketplace(category) {
                    document.getElementById("t9-content-area").innerHTML = '<p>Loading ' + category + '...</p>';
                    fetch(`/wp-json/t9suite/v1/marketplace?category=${category}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '<div style="display:flex; flex-wrap:wrap; gap:20px;">';
                            data.forEach(item => {
                                const localVersion = localStorage.getItem("t9module_version_" + item.id);
                                const remoteVersionMeta = item.meta_data?.find(m => m.key === '_version');
                                const remoteVersion = remoteVersionMeta?.value || '1.0.0';

                                let actionBtn = `<button class="button button-primary t9-download" data-id="${item.id}" data-version="${remoteVersion}">Download</button>`;
                                if (localVersion) {
                                    if (remoteVersion !== localVersion) {
                                        actionBtn = `<button class="button t9-update" data-id="${item.id}" data-version="${remoteVersion}">Update</button>`;
                                    } else {
                                        actionBtn = `<button class="button" disabled>Installed</button>`;
                                    }
                                }

                                html += `
                                    <div style="width:300px; border:1px solid #ddd; border-radius:8px; padding:10px;">
                                        <img src="${item.images[0]?.src}" style="width:100%; border-radius:4px;">
                                        <h3>${item.name}</h3>
                                        <p>${item.short_description}</p>
                                        <a href="${item.permalink}" target="_blank" class="button">Demo</a>
                                        ${actionBtn}
                                    </div>
                                `;
                            });
                            html += '</div>';
                            document.getElementById("t9-content-area").innerHTML = html;

                            document.querySelectorAll(".t9-download").forEach(btn => {
                                btn.addEventListener("click", function () {
                                    const productId = btn.dataset.id;
                                    const version = btn.dataset.version;
                                    document.getElementById("t9-license-modal").style.display = "flex";
                                    document.getElementById("t9-license-key").value = '';
                                    document.getElementById("t9-download-status").innerHTML = '';
                                    document.getElementById("t9-submit-license").dataset.productId = productId;
                                    document.getElementById("t9-submit-license").dataset.version = version;
                                });
                            });

                            document.querySelectorAll(".t9-update").forEach(btn => {
                                btn.addEventListener("click", function () {
                                    const productId = btn.dataset.id;
                                    const version = btn.dataset.version;
                                    const license = prompt("Enter license to update");
                                    if (!license) return;
                                    btn.innerText = 'Updating...';
                                    btn.disabled = true;

                                    fetch(t9adminProData.ajaxUrl, {
                                        method: "POST",
                                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                        body: new URLSearchParams({
                                            action: "t9admin_download_module",
                                            nonce: t9adminProData.nonce,
                                            license: license,
                                            product_id: productId
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            btn.innerText = 'Updated';
                                            localStorage.setItem("t9module_version_" + productId, version);
                                        } else {
                                            alert("❌ " + (data.data?.message || "Update failed"));
                                            btn.innerText = 'Update';
                                            btn.disabled = false;
                                        }
                                    });
                                });
                            });
                        });
                }

                document.querySelectorAll(".t9-tab").forEach(tab => {
                    tab.addEventListener("click", function (e) {
                        e.preventDefault();
                        document.querySelectorAll(".t9-tab").forEach(t => t.classList.remove("active"));
                        tab.classList.add("active");
                        loadMarketplace(tab.dataset.tab);
                    });
                });

                document.getElementById("t9-close-modal").onclick = () => {
                    document.getElementById("t9-license-modal").style.display = "none";
                };

                document.getElementById("t9-submit-license").onclick = () => {
                    const key = document.getElementById("t9-license-key").value;
                    const productId = document.getElementById("t9-submit-license").dataset.productId;
                    const version = document.getElementById("t9-submit-license").dataset.version;
                    const statusDiv = document.getElementById("t9-download-status");
                    statusDiv.innerHTML = "🔄 Sending...";

                    fetch(t9adminProData.ajaxUrl, {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: new URLSearchParams({
                            action: "t9admin_download_module",
                            nonce: t9adminProData.nonce,
                            license: key,
                            product_id: productId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            statusDiv.innerHTML = "✅ Module activated & downloaded";
                            const btn = document.querySelector(`.t9-download[data-id="${productId}"]`);
                            if (btn) {
                                btn.textContent = "Installed";
                                btn.disabled = true;
                                btn.classList.remove("button-primary");
                            }
                            localStorage.setItem("t9module_version_" + productId, version);
                            setTimeout(() => {
                                document.getElementById("t9-license-modal").style.display = "none";
                            }, 1000);
                        } else {
                            statusDiv.innerHTML = "❌ " + (data.data?.message || "Wrong license key");
                        }
                    })
                    .catch(() => {
                        statusDiv.innerHTML = "❌ Network error";
                    });
                };

                loadMarketplace('templates');
            });
            </script>
        </div>
