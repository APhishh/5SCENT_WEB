import { NextRequest, NextResponse } from 'next/server';
import { writeFile, mkdir } from 'fs/promises';
import { join } from 'path';
import { existsSync } from 'fs';

export async function POST(request: NextRequest) {
  try {
    const formData = await request.formData();
    const file = formData.get('file') as File;

    if (!file) {
      return NextResponse.json({ error: 'No file provided' }, { status: 400 });
    }

    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);

    // Create profile_pics directory if it doesn't exist
    const profilePicsDir = join(process.cwd(), 'public', 'profile_pics');
    if (!existsSync(profilePicsDir)) {
      await mkdir(profilePicsDir, { recursive: true });
    }

    // Generate unique filename
    const timestamp = Date.now();
    const filename = `${timestamp}-${file.name}`;
    const filepath = join(profilePicsDir, filename);

    // Write file to public/profile_pics
    await writeFile(filepath, buffer);

    // Return the path relative to public folder
    return NextResponse.json({ 
      path: `profile_pics/${filename}`,
      filename: filename
    });
  } catch (error: any) {
    console.error('Error uploading profile picture:', error);
    return NextResponse.json({ error: 'Failed to upload file' }, { status: 500 });
  }
}

