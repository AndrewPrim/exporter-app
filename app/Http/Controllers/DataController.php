<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DataExportRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataController extends Controller
{
    public function index(): View
    {
        return view('common.home.index', [
            'sqlFiles' => $this->getFilename('/sql_files'),
            'files' => $this->getFilename('/files')]);
    }

    public function exportData(DataExportRequest $request)
    {
       $request->validated();

       return  $this->{$request->typeExport}($request->sql_file);
    }

    public function txtExport(array $selectedFiles): RedirectResponse
    {
        $posts = $this->parseSqlPostsData($selectedFiles);

        $filename = sprintf('posts-%s.txt', now()->timestamp);
        $txtContent = null;

        foreach ($posts as $key => $post) {
            $txtContent .= "{$post['title']}" . "\n" . "{$post['content']}" . "\n\n\n";
        }

        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/plain',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
        ];


        Storage::put("/files/{$filename}", $txtContent);

        Response::make($txtContent, 200, $headers);

        return back()->with('success', 'File has been generated.');
    }

    public function xmlExport(array $selectedFiles): RedirectResponse
    {
        $posts = $this->parseSqlPostsData($selectedFiles);

        $filename = sprintf('posts-%s.xml', now()->timestamp);

        foreach ($posts as $data) {
            $xmlData['post'][] = $data;
        }

        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/xml',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
        ];

        $result = ArrayToXml::convert($xmlData, [], true, 'UTF-8', '1.1', [], true);

        Storage::put("/files/{$filename}", $result);

        Response::make($result, 200, $headers);
        return back()->with('success', 'File has been generated.');
    }

    public function csvExport(array $selectedFiles): StreamedResponse
    {
        $posts = $this->parseSqlPostsData($selectedFiles);

        $filename = sprintf('posts-%s.csv', now()->timestamp);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Title', 'Content'];

        $callback = function () use ($posts, $columns): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($posts as $key => $item) {
                $row['Title']  = $item['title'];
                $row['Content'] = $item['content'];

                fputcsv($file, array($row['Title'], $row['Content']), ";");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function parseSqlPostsData(array $selectedFiles): array
    {
        foreach ($selectedFiles as $key => $filePath){
            $sqlContent = File::get(storage_path("app/sql_files/{$filePath}"));

            $selectPattern =  "/(?:INSERT\sINTO\s[\`|'].*_posts[\`|\']\s\()(.*)(?=\))/";
            preg_match($selectPattern, $sqlContent, $insertSelect);


            $selectWithoutQuotes = preg_replace('/[\'|\`]/', "", $insertSelect[0]);
            $insertSelectArray = explode(", ", $selectWithoutQuotes);
            $postContentKey = array_search('post_content', $insertSelectArray);
            $postTitleKey = array_search('post_title', $insertSelectArray);

            // $pattern = "/(?:INSERT INTO '.*_posts'.*VALUES\s\()([^+]*)(?=\);\s)/U";
            $valuesPattern = "/(?:INSERT INTO [\'|\`].*_posts[\'|\`].*VALUES\s)([^+]*)(?=;\n)/U";
            preg_match($valuesPattern, $sqlContent, $valuesMatch);
            $valuesArray = explode(",\n", $valuesMatch[1]);

            for ($i = 0; $i < count($valuesArray); $i++) {
                $explodePostData = explode(",\t", $valuesArray[$i]);
                $postContent = $this->removeLinks($explodePostData[$postContentKey]);

                $postsData[] = array(
                    'title' => str_replace('\'', "", $explodePostData[$postTitleKey]),
                    'content' => str_replace('\'', "", $postContent),
                );
            }
        }

        return $postsData;
    }

    public function removeLinks(string $postContent): string
    {
        $pattern = "/(http|ftp|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])/";
        return preg_replace($pattern, "", $postContent);
    }

    public function download($filename): BinaryFileResponse
    {
        $filePath = storage_path("app/files/{$filename}");
        $headers = ['Content-Type: text/plain'];

        return response()->download($filePath, $filename, $headers);
    }

    public function getFilename(string $filePath): array
    {
        $files = Storage::disk('local')->files($filePath);
        $filenames = array_map(function($file){
            return basename($file);
        }, $files);

        return $filenames;
    }
}
