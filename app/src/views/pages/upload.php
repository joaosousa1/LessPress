<?php
// app/src/views/pages/upload.php

/**
 * LessPress Framework - Unified Upload and Resource Asset Management Dashboard
 * Standardized source view rendering form processors and active file indexes.
 * This file is processed by the build.php bundler pipeline.
 */

// 1. Resolve Runtime Dependencies
// Assuming your core boot wrapper exposes the PDO database instance via Database::connect([])
$pdoInstance = Database::connect([]);

// CORE FIX: Ensure the SQLite uploads table structure physically exists on disk 
// before executing any relational select or write queries!
Files::initTable($pdoInstance);

// Query all active metadata blocks bound to the currently authenticated identity
$userFiles = Files::findByUserId($pdoInstance, $_SESSION['user_id']);
?>

<div class="container">
    <section>
        <h1 style="margin-bottom: 5px;">📤 Secure Storage</h1>
        <p class="secondary">Upload and audit your application assets. Files are cryptographically unlinked from the public directory.</p>
        
        <hr />

        <?php if (isset($_GET['upload'])): ?>
            <?php if ($_GET['upload'] === 'success'): ?>
                <article style="border-left: 5px solid #2ecc71; background-color: rgba(46, 204, 113, 0.1); margin-bottom: 25px;">
                    <strong style="color: #2ecc71;">✓ Processing Complete</strong>
                    <p style="margin: 0; font-size: 0.95rem;">The resource artifact was safely stored, indexed, and restricted.</p>
                </article>
            <?php elseif ($_GET['upload'] === 'error'): ?>
                <article style="border-left: 5px solid #e74c3c; background-color: rgba(231, 76, 60, 0.1); margin-bottom: 25px;">
                    <strong style="color: #e74c3c;">⚠ Operation Faulted</strong>
                    <p style="margin: 0; font-size: 0.95rem;">Transaction rolled back. Please check file formatting or server post allocations.</p>
                </article>
            <?php endif; ?>
        <?php endif; ?>

        <article>
            <form action="/upload-handler" method="POST" enctype="multipart/form-data">
                <fieldset>
                    <label for="secure_image">
                        <strong>Choose New File Asset</strong>
                        <input 
                            type="file" 
                            id="secure_image" 
                            name="secure_image" 
                            accept="image/jpeg, image/png, image/webp, image/gif, application/pdf" 
                            required
                        >
                    </label>
                </fieldset>
                <div class="grid">
                    <button type="submit" class="primary">Upload</button>
                </div>
            </form>
        </article>

        <article style="margin-top: 40px;">
            <header>
                <strong>Active Asset Ledger (Scoped to Current Account)</strong>
            </header>
            
            <?php if (empty($userFiles)): ?>
                <p class="secondary" style="text-align: center; margin: 20px 0;">No secure files found on this profile registry. Run an upload above to populate data.</p>
            <?php else: ?>
                <div class="overflow-auto">
                    <table class="striped">
                        <thead>
                            <tr>
                                <th scope="col">Resource Format</th>
                                <th scope="col">Original Filename</th>
                                <th scope="col">Allocation Size</th>
                                <th scope="col">Uploaded Timestamp</th>
                                <th scope="col" style="text-align: right;">Action Gate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userFiles as $file): ?>
                                <tr>
                                    <td>
                                        <?php if ($file->mime_type === 'application/pdf'): ?>
                                            <span style="background: rgba(231, 76, 60, 0.15); color: #e74c3c; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">PDF</span>
                                        <?php else: ?>
                                            <span style="background: rgba(52, 152, 219, 0.15); color: #3498db; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">IMG</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td><code><?= htmlspecialchars($file->filename) ?></code></td>
                                    
                                    <td><?= round($file->file_size / 1024, 2) ?> KB</td>
                                    
                                    <td><?= htmlspecialchars($file->created_at) ?></td>
                                    
                                    <td style="text-align: right;">
                                        <a href="/view-file?id=<?= urlencode($file->id) ?>" target="_blank" role="button" class="outline contrast" style="padding: 4px 12px; font-size: 0.85rem;">
                                            View Resource
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>
    </section>
</div>